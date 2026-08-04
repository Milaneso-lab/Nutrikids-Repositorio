import os
from datetime import timedelta
from typing import Any

import requests
from dotenv import load_dotenv
from flask import Flask, jsonify, redirect, render_template, request, session, url_for
from flask_sqlalchemy import SQLAlchemy
from sqlalchemy import text

from user_messages import GENERIC_ERROR, mensaje_desde_api, sanitizar_mensaje

load_dotenv()

app = Flask(__name__)

# Firma las cookies de sesión del portal familiar: un valor por defecto conocido
# permitiría falsificar sesiones de padres.
_secret_key = os.getenv("FLASK_SECRET_KEY", "")
if len(_secret_key) < 32:
    raise RuntimeError(
        "FLASK_SECRET_KEY debe estar definida con al menos 32 caracteres aleatorios. "
        "Configúrala en el archivo .env antes de iniciar Flask."
    )
app.secret_key = _secret_key
app.config["PERMANENT_SESSION_LIFETIME"] = timedelta(days=14)
app.config["SESSION_COOKIE_SAMESITE"] = "Lax"
app.config["SESSION_COOKIE_HTTPONLY"] = True
# Se activa secure cookie si se detecta producción o https en la URL de laravel/flask
app.config["SESSION_COOKIE_SECURE"] = os.getenv("FLASK_COOKIE_SECURE", "False").lower() in ("true", "1", "yes")

_db_uri = os.getenv("SQLALCHEMY_DATABASE_URI")
if _db_uri:
    app.config["SQLALCHEMY_DATABASE_URI"] = _db_uri
    app.config["SQLALCHEMY_TRACK_MODIFICATIONS"] = False
    db = SQLAlchemy(app)
else:
    db = None  # Solo UI + API HTTP (sin conexión directa a BD)
API_BASE_URL = os.getenv("NUTRIKIDS_API_BASE_URL", "http://127.0.0.1:8000").rstrip("/")
LARAVEL_PUBLIC_URL = os.getenv("LARAVEL_PUBLIC_URL", "http://127.0.0.1:8080").rstrip("/")

# El bloque de credenciales de demo del login publica usuarios y contraseñas
# reales de los seeders. Se oculta salvo opt-in explícito para demos locales.
SHOW_DEMO_CREDENTIALS = os.getenv("FLASK_SHOW_DEMO_CREDENTIALS", "false").lower() in ("true", "1", "yes")


@app.after_request
def add_security_headers(response):
    response.headers["X-Content-Type-Options"] = "nosniff"
    response.headers["X-Frame-Options"] = "DENY"
    response.headers["X-XSS-Protection"] = "1; mode=block"
    response.headers["Referrer-Policy"] = "strict-origin-when-cross-origin"
    return response



@app.context_processor
def inject_globals():
    return {
        "laravel_public_url": LARAVEL_PUBLIC_URL,
        "api_base_url": API_BASE_URL,
        "mostrar_credenciales_demo": SHOW_DEMO_CREDENTIALS,
    }


@app.route("/health")
def health():
    return jsonify({"status": "ok", "service": "nutrikids-flask"}), 200


@app.route("/health/db")
def health_db():
    """Comprueba conexión a PostgreSQL (misma BD que Laravel y FastAPI)."""
    if db is None:
        return jsonify({"status": "skipped", "message": "SQLALCHEMY_DATABASE_URI no configurada"}), 200
    try:
        db.session.execute(text("SELECT 1"))
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 503
    return jsonify({"status": "ok", "database": "postgresql"})


@app.route("/docs")
def redirect_api_docs():
    """La documentación OpenAPI vive en FastAPI (puerto 8000), no en Flask."""
    return redirect(f"{API_BASE_URL}/docs")


@app.route("/openapi.json")
def redirect_openapi():
    return redirect(f"{API_BASE_URL}/openapi.json")


def api_headers(token: str | None = None) -> dict[str, str]:
    h: dict[str, str] = {"Accept": "application/json"}
    if token:
        h["Authorization"] = f"Bearer {token}"
    return h


def api_get(path: str, token: str | None = None) -> requests.Response:
    return requests.get(f"{API_BASE_URL}{path}", headers=api_headers(token), timeout=15)


def api_post(path: str, payload: dict[str, Any] | None = None, token: str | None = None, files=None) -> requests.Response:
    h = api_headers(token)
    if files is not None:
        return requests.post(f"{API_BASE_URL}{path}", headers=h, files=files, data=payload, timeout=15)
    h["Content-Type"] = "application/json"
    return requests.post(f"{API_BASE_URL}{path}", headers=h, json=payload or {}, timeout=15)


def api_put(path: str, payload: dict[str, Any], token: str | None = None) -> requests.Response:
    h = api_headers(token)
    h["Content-Type"] = "application/json"
    return requests.put(f"{API_BASE_URL}{path}", headers=h, json=payload, timeout=15)


def api_delete(path: str, token: str | None = None) -> requests.Response:
    return requests.delete(f"{API_BASE_URL}{path}", headers=api_headers(token), timeout=15)


def api_v1_get(path: str, token: str | None = None, params: dict | None = None) -> requests.Response:
    return requests.get(
        f"{API_BASE_URL}/api/v1{path}",
        headers=api_headers(token),
        params=params or {},
        timeout=15,
    )


def api_v1_post(path: str, payload: dict[str, Any] | None = None, token: str | None = None) -> requests.Response:
    h = api_headers(token)
    h["Content-Type"] = "application/json"
    return requests.post(f"{API_BASE_URL}/api/v1{path}", headers=h, json=payload or {}, timeout=15)


def _merge_api_errors(*errors: str | None) -> str | None:
    msgs = [e for e in errors if e]
    return msgs[0] if msgs else None


def _paginated_items(response: requests.Response) -> list[dict[str, Any]]:
    parsed = _response_json_safe(response)
    if isinstance(parsed, dict) and isinstance(parsed.get("data"), list):
        return parsed["data"]
    if isinstance(parsed, list):
        return parsed
    return []


def _require_padre_session():
    if session.get("rol") != "padre" or not session.get("token"):
        return redirect(url_for("login"))
    return None


def _fetch_ninos_enriched(token: str) -> tuple[list[dict[str, Any]], str | None]:
    """Lista niños del padre con IMC reciente (API v1)."""
    try:
        r = api_v1_get("/ninos", token=token, params={"per_page": 50})
        if not r.ok:
            return [], None
        ninos = _paginated_items(r)
    except requests.RequestException:
        return [], None

    enriched: list[dict[str, Any]] = []
    for nino in ninos:
        row = dict(nino)
        nino_id = row.get("id")
        if nino_id is None:
            enriched.append(row)
            continue
        try:
            ev_r = api_v1_get("/evaluaciones", token=token, params={"nino_id": nino_id, "per_page": 1})
            evals = _paginated_items(ev_r)
            if evals:
                ult = evals[0]
                row["ultimo_imc"] = ult.get("imc")
                peso = ult.get("peso_kg")
                if peso and row.get("ultimo_imc"):
                    imc = float(row["ultimo_imc"]) if row["ultimo_imc"] else None
                    if imc is not None:
                        if imc < 18.5:
                            row["clasificacion_imc"] = "Bajo peso"
                        elif imc < 25:
                            row["clasificacion_imc"] = "Normal"
                        else:
                            row["clasificacion_imc"] = "Sobrepeso"
        except requests.RequestException:
            pass
        enriched.append(row)
    return enriched, None


def _fetch_citas_padre(token: str) -> tuple[list[dict[str, Any]], str | None]:
    try:
        r = api_v1_get("/citas", token=token, params={"per_page": 50})
        if r.ok:
            return _paginated_items(r), None
        return [], None
    except requests.RequestException:
        return [], None


# --- Portal familiar (padres autenticados) ---


@app.route("/portal")
def portal_dashboard():
    guard = _require_padre_session()
    if guard:
        return guard
    token = session.get("token")
    ninos, ninos_err = _fetch_ninos_enriched(token)
    citas, citas_err = _fetch_citas_padre(token)
    api_error = _merge_api_errors(ninos_err, citas_err)
    total_habitos = 0
    for nino in ninos:
        nid = nino.get("id")
        if not nid:
            continue
        try:
            h_r = api_v1_get(f"/ninos/{nid}/habitos", token=token, params={"per_page": 50})
            if h_r.ok:
                total_habitos += len(_paginated_items(h_r))
        except requests.RequestException:
            continue
    return render_template(
        "portal/dashboard.html",
        active_nav="dashboard",
        ninos=ninos,
        citas=citas,
        total_habitos=total_habitos,
        api_error=api_error,
    )


@app.route("/portal/hijos")
def portal_hijos():
    guard = _require_padre_session()
    if guard:
        return guard
    ninos, api_error = _fetch_ninos_enriched(session.get("token"))
    return render_template("portal/hijos.html", active_nav="hijos", ninos=ninos, api_error=api_error)


@app.route("/portal/hijos/<int:nino_id>")
def portal_hijo_detalle(nino_id: int):
    guard = _require_padre_session()
    if guard:
        return guard
    token = session.get("token")
    nino: dict[str, Any] = {}
    evaluaciones: list[dict[str, Any]] = []
    menus: list[dict[str, Any]] = []
    api_error: str | None = None
    try:
        ninos, ninos_err = _fetch_ninos_enriched(token)
        api_error = ninos_err
        nino = next((n for n in ninos if n.get("id") == nino_id), {})
        ev_r = api_v1_get("/evaluaciones", token=token, params={"nino_id": nino_id, "per_page": 20})
        if ev_r.ok:
            evaluaciones = _paginated_items(ev_r)
        elif not api_error:
            api_error = None
        menu_r = api_v1_get("/menus", token=token, params={"nino_id": nino_id, "per_page": 20})
        if menu_r.ok:
            menus = _paginated_items(menu_r)
    except requests.RequestException:
        api_error = api_error or None
    ultima_eval = evaluaciones[0] if evaluaciones else {}
    return render_template(
        "portal/hijo_detalle.html",
        active_nav="hijos",
        nino=nino,
        evaluaciones=evaluaciones,
        menus=menus,
        ultima_eval=ultima_eval,
        api_error=api_error,
    )


@app.route("/portal/citas")
def portal_citas():
    guard = _require_padre_session()
    if guard:
        return guard
    citas, api_error = _fetch_citas_padre(session.get("token"))
    return render_template("portal/citas.html", active_nav="citas", citas=citas, api_error=api_error)


@app.route("/portal/habitos")
def portal_habitos():
    guard = _require_padre_session()
    if guard:
        return guard
    token = session.get("token")
    habitos_por_nino: list[dict[str, Any]] = []
    ninos, api_error = _fetch_ninos_enriched(token)
    for nino in ninos:
        nid = nino.get("id")
        habitos: list[dict[str, Any]] = []
        if nid:
            try:
                h_r = api_v1_get(f"/ninos/{nid}/habitos", token=token, params={"per_page": 50})
                if h_r.ok:
                    habitos = _paginated_items(h_r)
            except requests.RequestException:
                pass
        habitos_por_nino.append(
            {
                "nombre": f"{nino.get('nombre', 'Niño')} {nino.get('apellidos', '')}".strip(),
                "habitos": habitos,
            }
        )
    return render_template("portal/habitos.html", active_nav="habitos", habitos_por_nino=habitos_por_nino, api_error=api_error)


@app.route("/portal/notificaciones")
def portal_notificaciones():
    guard = _require_padre_session()
    if guard:
        return guard
    token = session.get("token")
    notificaciones: list[dict[str, Any]] = []
    citas, citas_err = _fetch_citas_padre(token)
    api_error = citas_err
    for cita in citas:
        estado = cita.get("estado", "pendiente")
        notificaciones.append(
            {
                "titulo": f"Cita {estado.replace('_', ' ')}",
                "mensaje": f"Fecha preferida: {cita.get('fecha_preferida', '—')} ({cita.get('franja', 'manana')})",
                "fecha": cita.get("updated_at") or cita.get("created_at") or "",
            }
        )
    try:
        alert_r = api_v1_get("/alertas", token=token, params={"per_page": 10, "atendida": False})
        if alert_r.ok:
            for alerta in _paginated_items(alert_r):
                notificaciones.append(
                    {
                        "titulo": alerta.get("titulo", "Alerta clínica"),
                        "mensaje": alerta.get("mensaje", alerta.get("descripcion", "")),
                        "fecha": alerta.get("created_at", ""),
                    }
                )
    except requests.RequestException:
        pass
    return render_template(
        "portal/notificaciones.html",
        active_nav="notificaciones",
        notificaciones=notificaciones,
        api_error=api_error,
    )


@app.route("/portal/cuenta")
def portal_cuenta():
    guard = _require_padre_session()
    if guard:
        return guard
    return render_template("portal/cuenta.html", active_nav="cuenta")


def _response_json_safe(r: requests.Response) -> dict[str, Any] | list[Any] | None:
    if not r.content or not r.content.strip():
        return None
    try:
        data = r.json()
    except ValueError:
        return None
    return data if isinstance(data, (dict, list)) else None


def _fastapi_error_detail(body: dict[str, Any] | None) -> str | None:
    if not body:
        return None
    msg = body.get("detail")
    if isinstance(msg, str):
        return msg
    if isinstance(msg, list):
        parts: list[str] = []
        for item in msg:
            if isinstance(item, dict) and item.get("msg"):
                parts.append(str(item["msg"]))
            else:
                parts.append(str(item))
        return "; ".join(parts) if parts else None
    return None


# --- Páginas estáticas (padres) ---


@app.route("/")
def index():
    return render_template("index.html")


@app.route("/Obesidad")
def obesidad():
    return render_template("Obesidad.html")


@app.route("/calculadora")
def calculadora():
    return render_template("calculadora.html")


@app.route("/nutriologos")
def nutriologos():
    return render_template("nutriologos.html")


@app.route("/Comentarios")
def comentarios():
    return render_template("Comentarios.html")


@app.route("/Foros")
def foros():
    return render_template("Foros.html")


@app.route("/conocenos")
def conocenos():
    return render_template("conocenos.html")


@app.route("/Contacto")
def contacto_page():
    return render_template("Contacto.html")


@app.route("/login", methods=["GET", "POST"])
def login():
    if request.method == "POST" and request.is_json:
        return iniciar_sesion()
    if session.get("rol") == "padre":
        return redirect(url_for("index"))
    return render_template("login_full.html")


@app.route("/dashboard")
def dashboard():
    """Redirige al portal familiar si el padre está autenticado."""
    if session.get("rol") == "padre" and session.get("token"):
        return redirect(url_for("portal_dashboard"))
    return redirect(url_for("login"))


@app.route("/logout", methods=["POST"])
def logout():
    session.clear()
    return redirect(url_for("login"))


# --- API proxy (misma forma que Laravel) ---


@app.route("/InsertarContacto", methods=["POST"])
def insertar_contacto():
    if request.is_json:
        data = request.get_json() or {}
    else:
        data = {
            "nombre": request.form.get("nombre", ""),
            "apellido": request.form.get("apellido", ""),
            "email": request.form.get("email", ""),
            "mensaje": request.form.get("mensaje", ""),
        }
    try:
        r = api_post("/api/contactos", data)
        if r.status_code >= 400:
            body = r.json() if r.headers.get("content-type", "").startswith("application/json") else {}
            msg = mensaje_desde_api(body if isinstance(body, dict) else None)
            return jsonify({"success": False, "message": msg}), r.status_code
        return jsonify({"success": True, "message": "¡Mensaje enviado correctamente! Gracias por contactarnos."})
    except requests.RequestException:
        return jsonify({"success": False, "message": GENERIC_ERROR}), 500


@app.route("/ObtenerComentarios", methods=["GET"])
def obtener_comentarios():
    """Lista pública de comentarios (misma API que admin); GET /api/comentarios no requiere token."""
    try:
        r = api_get("/api/comentarios", None)
        if not r.ok:
            return jsonify({"success": True, "comentarios": [], "usuario_actual": session.get("user_id"), "es_padre": session.get("rol") == "padre"})
        rows = r.json()
        return jsonify({
            "success": True,
            "comentarios": rows if isinstance(rows, list) else [],
            "usuario_actual": session.get("user_id"),
            "es_padre": session.get("rol") == "padre",
        })
    except requests.RequestException:
        return jsonify({"success": True, "comentarios": [], "usuario_actual": session.get("user_id"), "es_padre": session.get("rol") == "padre"})


@app.route("/InsertarComentario", methods=["POST"])
def insertar_comentario():
    tok = session.get("token")
    if session.get("rol") != "padre" or not tok:
        return jsonify({"success": False, "message": "Debes iniciar sesión como padre para publicar comentarios."}), 401
    body = request.get_json() if request.is_json else request.form.to_dict()
    comentario = body.get("comentario", "")
    id_padre = body.get("id_comentario_padre")
    payload = {
        "nombre": session.get("nombre", "Usuario"),
        "apellido": session.get("apellido", ""),
        "comentario": comentario,
        "id_usuario": session.get("user_id"),
    }
    if id_padre:
        try:
            payload["id_comentario_padre"] = int(id_padre)
        except (TypeError, ValueError):
            return jsonify({"success": False, "message": "Comentario padre inválido."}), 400
    try:
        r = api_post("/api/comentarios", payload, token=tok)
        if r.status_code >= 400:
            body = r.json() if r.content else {}
            msg = mensaje_desde_api(body if isinstance(body, dict) else None)
            return jsonify({"success": False, "message": msg}), r.status_code
        return jsonify({"success": True, "message": "¡Comentario publicado correctamente!", "comentario": r.json()})
    except requests.RequestException:
        return jsonify({"success": False, "message": GENERIC_ERROR}), 500


@app.route("/SolicitarCita", methods=["POST"])
def solicitar_cita():
    tok = session.get("token")
    if session.get("rol") != "padre" or not tok:
        return (
            jsonify(
                {
                    "success": False,
                    "message": "Debes iniciar sesión como padre para solicitar una cita.",
                }
            ),
            401,
        )
    data = request.get_json() if request.is_json else request.form.to_dict()
    franja = (data.get("franja") or "manana").lower()
    if franja not in ("manana", "tarde"):
        franja = "manana"
    payload = {
        "fecha_preferida": (data.get("fecha_preferida") or "").strip(),
        "franja": franja,
        "telefono": (data.get("telefono") or "").strip() or None,
        "mensaje": (data.get("mensaje") or "").strip() or None,
    }
    if not payload["fecha_preferida"]:
        return jsonify({"success": False, "message": "Indica la fecha preferida para la cita."}), 400
    try:
        r = api_v1_post("/citas", payload, token=tok)
    except requests.RequestException:
        return jsonify({"success": False, "message": GENERIC_ERROR}), 503

    parsed = _response_json_safe(r)

    if r.status_code >= 400:
        body = parsed if isinstance(parsed, dict) else {}
        msg = mensaje_desde_api(body)
        code = r.status_code if 400 <= r.status_code < 600 else 502
        return jsonify({"success": False, "message": msg}), code

    if not isinstance(parsed, dict):
        return jsonify({"success": False, "message": GENERIC_ERROR}), 502

    return jsonify(
        {
            "success": True,
            "message": "Tu solicitud fue enviada. Un nutriólogo o el equipo te contactará pronto.",
            "cita": parsed,
        }
    )


@app.route("/ObtenerDiscusiones", methods=["GET"])
def obtener_discusiones():
    try:
        r = api_get("/api/discusiones")
        if not r.ok:
            return jsonify({"success": True, "discusiones": [], "usuario_actual": session.get("user_id"), "es_padre": session.get("rol") == "padre"})
        rows = r.json()
        return jsonify(
            {
                "success": True,
                "discusiones": rows,
                "usuario_actual": session.get("user_id"),
                "es_padre": session.get("rol") == "padre",
            }
        )
    except requests.RequestException:
        return jsonify({"success": True, "discusiones": [], "usuario_actual": session.get("user_id"), "es_padre": session.get("rol") == "padre"})


@app.route("/InsertarDiscusion", methods=["POST"])
def insertar_discusion():
    tok = session.get("token")
    if session.get("rol") != "padre" or not tok:
        return jsonify({"success": False, "message": "Debes iniciar sesión como padre para crear discusiones."}), 401
    data = request.get_json() if request.is_json else request.form.to_dict()
    try:
        r = api_post(
            "/api/discusiones",
            {
                "tema": data.get("tema", ""),
                "descripcion": data.get("descripcion", ""),
                "id_usuario": session.get("user_id"),
            },
            token=tok,
        )
        if r.status_code >= 400:
            body = r.json() if r.content else {}
            msg = mensaje_desde_api(body if isinstance(body, dict) else None)
            return jsonify({"success": False, "message": msg}), r.status_code
        return jsonify({"success": True, "message": "¡Discusión creada exitosamente!", "discusion": r.json()})
    except requests.RequestException:
        return jsonify({"success": False, "message": GENERIC_ERROR}), 500


@app.route("/InsertarRespuestaDiscusion/<int:did>", methods=["POST"])
def insertar_respuesta_discusion(did):
    tok = session.get("token")
    if session.get("rol") != "padre" or not tok:
        return jsonify({"success": False, "message": "Debes iniciar sesión como padre para responder."}), 401
    data = request.get_json() if request.is_json else request.form.to_dict()
    mensaje = (data.get("mensaje") or "").strip()
    if len(mensaje) < 5:
        return jsonify({"success": False, "message": "La respuesta debe tener al menos 5 caracteres."}), 400
    try:
        r = api_post(f"/api/discusiones/{did}/respuestas", {"mensaje": mensaje}, token=tok)
        if r.status_code >= 400:
            body = r.json() if r.content else {}
            msg = mensaje_desde_api(body if isinstance(body, dict) else None)
            return jsonify({"success": False, "message": msg}), r.status_code
        return jsonify({"success": True, "message": "¡Respuesta publicada!", "respuesta": r.json()})
    except requests.RequestException:
        return jsonify({"success": False, "message": GENERIC_ERROR}), 500


@app.route("/ActualizarDiscusion/<int:did>", methods=["POST"])
def actualizar_discusion(did):
    tok = session.get("token")
    if session.get("rol") != "padre" or not tok:
        return jsonify({"success": False, "message": "No autorizado."}), 401
    data = request.get_json() if request.is_json else request.form.to_dict()
    try:
        r = api_put(f"/api/discusiones/{did}", {"tema": data.get("tema"), "descripcion": data.get("descripcion")}, token=tok)
        if r.status_code == 404:
            return jsonify({"success": False, "message": "No encontrado."}), 404
        if r.status_code >= 400:
            body = r.json() if r.content else {}
            msg = mensaje_desde_api(body if isinstance(body, dict) else None)
            return jsonify({"success": False, "message": msg}), r.status_code
        return jsonify({"success": True, "message": "Actualizado.", "discusion": r.json()})
    except requests.RequestException:
        return jsonify({"success": False, "message": GENERIC_ERROR}), 500


@app.route("/EliminarDiscusion/<int:did>", methods=["POST"])
def eliminar_discusion(did):
    tok = session.get("token")
    if session.get("rol") != "padre" or not tok:
        return jsonify({"success": False, "message": "No autorizado."}), 401
    try:
        r = api_delete(f"/api/discusiones/{did}", token=tok)
        if r.status_code >= 400:
            body = r.json() if r.content else {}
            msg = mensaje_desde_api(body if isinstance(body, dict) else None)
            return jsonify({"success": False, "message": msg}), r.status_code
        return jsonify({"success": True, "message": "Eliminado."})
    except requests.RequestException:
        return jsonify({"success": False, "message": GENERIC_ERROR}), 500


@app.route("/IniciarSesion", methods=["POST"])
def iniciar_sesion():
    data = request.get_json() if request.is_json else request.form.to_dict()
    email = data.get("email", "")
    contrasena = data.get("contrasena", "")
    try:
        r = api_v1_post("/auth/login", {"email": email, "contrasena": contrasena})
        if not r.ok:
            body = r.json() if r.headers.get("content-type", "").startswith("application/json") else {}
            msg = mensaje_desde_api(body if isinstance(body, dict) else None, "Email o contraseña incorrectos.")
            return jsonify({"success": False, "message": msg}), r.status_code
        out = r.json()
        rol = out.get("rol", "")
        if rol == "admin":
            laravel = LARAVEL_PUBLIC_URL
            return jsonify(
                {
                    "success": True,
                    "message": "Inicio de sesión correcto. Redirigiendo…",
                    "laravel_login_required": True,
                    "laravel_iniciar_sesion_url": f"{laravel}/IniciarSesion",
                    "rol": rol,
                }
            )
        if rol == "nutriologo":
            laravel = LARAVEL_PUBLIC_URL
            return jsonify(
                {
                    "success": True,
                    "message": "Inicio de sesión correcto. Redirigiendo…",
                    "laravel_login_required": True,
                    "laravel_iniciar_sesion_url": f"{laravel}/IniciarSesion",
                    "rol": rol,
                }
            )
        if rol == "padre":
            session.permanent = True
            session["token"] = out.get("access_token")
            session["refresh_token"] = out.get("refresh_token")
            session["rol"] = "padre"
            try:
                session["user_id"] = int(out.get("id_usuario"))
            except (TypeError, ValueError):
                session["user_id"] = out.get("id_usuario")
            session["nombre"] = out.get("nombre") or data.get("nombre") or "Padre"
            session["apellido"] = (out.get("apellido_paterno") or "").strip()
            session["email"] = out.get("email") or email
            return jsonify(
                {
                    "success": True,
                    "message": "¡Inicio de sesión exitoso!",
                    "redirect": "/portal",
                    "rol": rol,
                }
            )
        return jsonify({"success": False, "message": "Tu tipo de cuenta no puede acceder desde aquí."}), 400
    except requests.RequestException:
        return jsonify({"success": False, "message": GENERIC_ERROR}), 500


@app.route("/RegistrarUsuario", methods=["POST"])
def registrar_usuario():
    data = request.get_json() if request.is_json else request.form.to_dict()
    payload = {
        "nombre": data.get("nombre", ""),
        "apellido_paterno": data.get("apellido_paterno", ""),
        "apellido_materno": data.get("apellido_materno") or None,
        "email": data.get("email", ""),
        "contrasena": data.get("contrasena", ""),
    }
    try:
        r = api_v1_post("/auth/register", payload)
        if r.status_code in (401, 403):
            return jsonify({"success": False, "message": "No se pudo registrar."}), r.status_code
        if r.status_code >= 400:
            body = r.json() if r.content else {}
            msg = mensaje_desde_api(body if isinstance(body, dict) else None, "No se pudo completar el registro.")
            return jsonify({"success": False, "message": msg}), r.status_code
        return jsonify({"success": True, "message": "Usuario registrado.", "user": r.json()})
    except requests.RequestException:
        return jsonify({"success": False, "message": GENERIC_ERROR}), 500


if __name__ == "__main__":
    _debug = os.getenv("FLASK_DEBUG", "false").lower() in ("true", "1", "yes")
    _port = int(os.getenv("PORT", os.getenv("FLASK_PORT", "5000")))
    app.run(host="0.0.0.0", port=_port, debug=_debug)
