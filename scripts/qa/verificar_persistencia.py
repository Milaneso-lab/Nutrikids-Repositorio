"""Verificación funcional de persistencia de NutriKids.

Ejecuta el CRUD real contra la API (FastAPI), el panel web (Laravel) y comprueba
en PostgreSQL que cada operación dejó la fila esperada. No usa dependencias
externas más allá de psycopg2, que ya está instalado en el contenedor de la API.

Uso (desde la raíz del repositorio, en PowerShell):

    Get-Content scripts/qa/verificar_persistencia.py | docker compose exec -T fastapi python -

Los datos que crea usan el dominio @qanutrikids.com y se eliminan al terminar.
"""

from __future__ import annotations

import html
import json
import os
import random
import re
import urllib.error
import urllib.parse
import urllib.request
from http.cookiejar import CookieJar

API = os.getenv("QA_API_URL", "http://127.0.0.1:8000")
WEB = os.getenv("QA_WEB_URL", "http://laravel:80")

SUFIJO = random.randint(10000, 99999)
resultados: list[tuple[str, bool, str]] = []


def registrar(nombre: str, ok: bool, detalle: str = "") -> None:
    resultados.append((nombre, ok, detalle))
    print(f"[{'OK ' if ok else 'FALLA'}] {nombre}" + (f" — {detalle}" if detalle else ""), flush=True)


# --------------------------------------------------------------------------
# Utilidades HTTP
# --------------------------------------------------------------------------

def peticion(url, metodo="GET", datos=None, cabeceras=None, opener=None, form=False):
    cabeceras = dict(cabeceras or {})
    cuerpo = None

    if datos is not None:
        if form:
            cuerpo = urllib.parse.urlencode(datos).encode()
            cabeceras.setdefault("Content-Type", "application/x-www-form-urlencoded")
        else:
            cuerpo = json.dumps(datos).encode()
            cabeceras.setdefault("Content-Type", "application/json")

    req = urllib.request.Request(url, data=cuerpo, headers=cabeceras, method=metodo)
    abrir = opener.open if opener else urllib.request.urlopen

    try:
        with abrir(req, timeout=30) as resp:
            return resp.status, resp.read().decode("utf-8", "replace")
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode("utf-8", "replace")
    except Exception as e:  # noqa: BLE001
        return 0, f"{type(e).__name__}: {e}"


def json_o_none(texto):
    try:
        return json.loads(texto)
    except Exception:  # noqa: BLE001
        return None


# --------------------------------------------------------------------------
# PostgreSQL
# --------------------------------------------------------------------------

def conectar_bd():
    import psycopg2

    url = os.environ["NUTRIKIDS_DATABASE_URL"].replace("postgresql+psycopg2://", "postgresql://")
    return psycopg2.connect(url)


def consultar(sql, params=()):
    with conectar_bd() as cn, cn.cursor() as cur:
        cur.execute(sql, params)
        return cur.fetchall()


# --------------------------------------------------------------------------
# API (los mismos endpoints que consume la app móvil)
# --------------------------------------------------------------------------

def login_api(email, contrasena):
    estado, cuerpo = peticion(f"{API}/api/v1/auth/login", "POST", {"email": email, "contrasena": contrasena})
    datos = json_o_none(cuerpo) or {}
    return datos.get("access_token"), estado, cuerpo


def verificar_api():
    print("\n=== API REST (contrato que consume la app móvil) ===", flush=True)

    token, estado, cuerpo = login_api("admin@nutrikids.com", "Admin123*")
    registrar("API · iniciar sesión (admin)", bool(token), f"HTTP {estado}")
    if not token:
        print(cuerpo[:300])
        return None

    auth = {"Authorization": f"Bearer {token}"}
    email = f"qa.api.{SUFIJO}@qanutrikids.com"

    # CREAR
    estado, cuerpo = peticion(
        f"{API}/api/v1/usuarios",
        "POST",
        {
            "nombre": "QaApi",
            "apellido_paterno": "Persistencia",
            "apellido_materno": "Test",
            "email": email,
            "contrasena": "Passw0rd*2026",
            "rol": "nutriologo",
            "telefono": "5550000000",
        },
        auth,
    )
    creado = json_o_none(cuerpo) or {}
    user_id = creado.get("id_usuario")
    registrar("API · crear usuario", estado == 201 and bool(user_id), f"HTTP {estado} id={user_id}")

    if user_id:
        filas = consultar("SELECT rol, rol_id FROM usuarios WHERE id_usuario=%s", (user_id,))
        ok = bool(filas) and filas[0][1] is not None
        registrar(
            "API · usuario persistido en PostgreSQL con rol_id",
            ok,
            f"rol={filas[0][0]} rol_id={filas[0][1]}" if filas else "sin fila",
        )

        # EDITAR
        estado, cuerpo = peticion(
            f"{API}/api/v1/usuarios/{user_id}", "PUT", {"nombre": "QaApiEditado", "telefono": "5559999999"}, auth
        )
        filas = consultar("SELECT nombre, telefono FROM usuarios WHERE id_usuario=%s", (user_id,))
        ok = estado == 200 and filas and filas[0][0] == "QaApiEditado" and filas[0][1] == "5559999999"
        registrar("API · editar usuario", bool(ok), f"HTTP {estado} bd={filas[0] if filas else None}")

        # CORREO DUPLICADO
        estado, cuerpo = peticion(
            f"{API}/api/v1/usuarios",
            "POST",
            {
                "nombre": "Dup",
                "apellido_paterno": "Dup",
                "email": email,
                "contrasena": "Passw0rd*2026",
                "rol": "padre",
            },
            auth,
        )
        datos = json_o_none(cuerpo) or {}
        mensaje = (datos.get("error") or {}).get("message", "")
        registrar("API · rechaza correo duplicado con mensaje claro", estado == 409 and bool(mensaje), f"HTTP {estado} «{mensaje}»")

        # SIN TOKEN
        estado, _ = peticion(f"{API}/api/v1/usuarios", "GET")
        registrar("API · rechaza petición sin token", estado in (401, 403), f"HTTP {estado}")

        # ELIMINAR
        estado, cuerpo = peticion(f"{API}/api/v1/usuarios/{user_id}", "DELETE", None, auth)
        filas = consultar("SELECT count(*) FROM usuarios WHERE id_usuario=%s", (user_id,))
        ok = estado == 204 and filas[0][0] == 0
        registrar("API · eliminar usuario", bool(ok), f"HTTP {estado} filas_restantes={filas[0][0]}")

    return auth


# --------------------------------------------------------------------------
# Panel web (Laravel)
# --------------------------------------------------------------------------

def sesion_web(email, contrasena):
    cj = CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))
    peticion(f"{WEB}/up", opener=opener)
    estado, cuerpo = peticion(
        f"{WEB}/IniciarSesion", "POST", {"email": email, "contrasena": contrasena}, {"Accept": "application/json"}, opener
    )
    datos = json_o_none(cuerpo) or {}
    return opener, datos.get("success") is True, estado


def token_csrf(opener, ruta):
    _, cuerpo = peticion(f"{WEB}{ruta}", cabeceras={"Accept": "text/html"}, opener=opener)
    m = re.search(r'name="_token"\s+value="([^"]+)"', cuerpo)
    return (m.group(1) if m else None), cuerpo


def opciones_select(html_texto, campo):
    bloque = re.search(rf'<select[^>]*name="{campo}".*?</select>', html_texto, re.S)
    if not bloque:
        return []
    return re.findall(r'<option\s+value="(\d+)"', bloque.group(0))


def verificar_web():
    print("\n=== PANEL WEB (Laravel) ===", flush=True)

    opener, ok_login, estado = sesion_web("admin@nutrikids.com", "Admin123*")
    registrar("Web · iniciar sesión (admin)", ok_login, f"HTTP {estado}")
    if not ok_login:
        return

    email = f"qa.web.{SUFIJO}@qanutrikids.com"
    tok, _ = token_csrf(opener, "/admin/usuarios/crear")

    # CREAR usuario desde el formulario
    estado, cuerpo = peticion(
        f"{WEB}/admin/usuarios",
        "POST",
        {
            "_token": tok,
            "nombre": "QaWeb",
            "apellido_paterno": "Persistencia",
            "apellido_materno": "Test",
            "email": email,
            "contrasena": "Passw0rd*2026",
            "rol": "nutriologo",
        },
        {"Accept": "text/html"},
        opener,
        form=True,
    )
    filas = consultar("SELECT id_usuario, rol_id FROM usuarios WHERE email=%s", (email,))
    registrar("Web · crear usuario", bool(filas), f"HTTP {estado} id={filas[0][0] if filas else None}")
    registrar(
        "Web · mensaje de éxito visible tras crear",
        "creado correctamente" in cuerpo,
        "banner de confirmación en la respuesta",
    )
    registrar("Web · rol_id poblado (causa raíz original)", bool(filas) and filas[0][1] is not None,
              f"rol_id={filas[0][1] if filas else None}")

    user_id = filas[0][0] if filas else None

    # CORREO DUPLICADO -> vuelve al formulario con el error
    if user_id:
        tok2, _ = token_csrf(opener, "/admin/usuarios/crear")
        estado, cuerpo = peticion(
            f"{WEB}/admin/usuarios",
            "POST",
            {
                "_token": tok2,
                "nombre": "QaWebDup",
                "apellido_paterno": "Persistencia",
                "email": email,
                "contrasena": "Passw0rd*2026",
                "rol": "nutriologo",
            },
            {"Accept": "text/html"},
            opener,
            form=True,
        )
        texto = html.unescape(cuerpo)
        es_html = "<html" in cuerpo.lower()
        # Comparación sin acentos: el script puede llegar al contenedor por una
        # tubería que no preserva UTF-8.
        menciona_duplicado = "email ya est" in texto
        registrar(
            "Web · error de correo duplicado legible (no JSON crudo)",
            menciona_duplicado and es_html,
            f"html={es_html} mensaje_presente={menciona_duplicado}",
        )
        registrar("Web · conserva lo escrito tras el error", "QaWebDup" in cuerpo, "old input")

    # EDITAR usuario (AJAX, como la interfaz)
    if user_id:
        tok3, _ = token_csrf(opener, f"/admin/usuarios/{user_id}/editar")
        estado, cuerpo = peticion(
            f"{WEB}/admin/usuarios/{user_id}",
            "POST",
            {
                "_token": tok3,
                "_method": "PUT",
                "nombre": "QaWebEditado",
                "apellido_paterno": "Persistencia",
                "apellido_materno": "Test",
                "email": email,
                "rol": "admin",
            },
            {"Accept": "application/json", "X-Requested-With": "XMLHttpRequest"},
            opener,
            form=True,
        )
        filas = consultar(
            "SELECT u.nombre, u.rol, r.nombre FROM usuarios u JOIN roles r ON r.id=u.rol_id WHERE u.id_usuario=%s",
            (user_id,),
        )
        ok = estado == 200 and filas and filas[0][0] == "QaWebEditado"
        registrar("Web · editar usuario", bool(ok), f"HTTP {estado} bd={filas[0] if filas else None}")
        registrar(
            "Web · cambio de rol sincroniza rol_id",
            bool(filas) and filas[0][1] == filas[0][2] == "admin",
            f"rol={filas[0][1]} roles.nombre={filas[0][2]}" if filas else "",
        )

    # ELIMINAR usuario
    if user_id:
        tok4, _ = token_csrf(opener, f"/admin/usuarios/{user_id}/editar")
        estado, cuerpo = peticion(
            f"{WEB}/admin/usuarios/{user_id}",
            "POST",
            {"_token": tok4, "_method": "DELETE"},
            {"Accept": "application/json", "X-Requested-With": "XMLHttpRequest"},
            opener,
            form=True,
        )
        filas = consultar("SELECT count(*) FROM usuarios WHERE id_usuario=%s", (user_id,))
        registrar("Web · eliminar usuario", estado == 200 and filas[0][0] == 0, f"HTTP {estado}")

    # CONFIGURACIÓN (antes devolvía éxito sin guardar)
    tok5, _ = token_csrf(opener, "/admin/configuracion")
    valor = f"NutriKids QA {SUFIJO}"
    estado, cuerpo = peticion(
        f"{WEB}/admin/configuracion",
        "POST",
        {"_token": tok5, "nombre_sistema": valor, "email_contacto": "qa@nutrikids.com"},
        {"Accept": "application/json", "X-Requested-With": "XMLHttpRequest"},
        opener,
        form=True,
    )
    filas = consultar("SELECT valor FROM configuraciones WHERE clave='nombre_sistema'")
    registrar("Web · configuración se guarda en PostgreSQL", bool(filas) and filas[0][0] == valor,
              f"bd={filas[0][0] if filas else None}")

    # INSTITUCIONES (antes en un JSON en disco)
    tok6, _ = token_csrf(opener, "/admin/instituciones")
    nombre_inst = f"Escuela QA {SUFIJO}"
    estado, cuerpo = peticion(
        f"{WEB}/admin/instituciones",
        "POST",
        {"_token": tok6, "nombre": nombre_inst, "tipo": "escuela", "ciudad": "Puebla"},
        {"Accept": "text/html"},
        opener,
        form=True,
    )
    filas = consultar("SELECT count(*) FROM instituciones WHERE nombre=%s", (nombre_inst,))
    registrar("Web · institución se guarda en PostgreSQL", filas[0][0] == 1, f"HTTP {estado}")

    return opener


def verificar_nutriologo():
    print("\n=== PANEL NUTRIÓLOGO (Laravel) ===", flush=True)

    opener, ok_login, estado = sesion_web("nutriologo@nutrikids.com", "Nutri123*")
    registrar("Web · iniciar sesión (nutriólogo)", ok_login, f"HTTP {estado}")
    if not ok_login:
        return

    # CREAR paciente
    tok, _ = token_csrf(opener, "/nutriologo/pacientes/crear")
    nombre_pac = f"PacQA{SUFIJO}"
    estado, _ = peticion(
        f"{WEB}/nutriologo/pacientes",
        "POST",
        {"_token": tok, "nombre": nombre_pac, "apellidos": "Persistencia", "fecha_nacimiento": "2017-03-15"},
        {"Accept": "text/html"},
        opener,
        form=True,
    )
    filas = consultar("SELECT id FROM pacientes WHERE nombre=%s", (nombre_pac,))
    paciente_id = filas[0][0] if filas else None
    registrar("Web · crear paciente", bool(paciente_id), f"HTTP {estado} id={paciente_id}")

    # EDITAR paciente
    if paciente_id:
        tok, _ = token_csrf(opener, f"/nutriologo/pacientes/{paciente_id}/editar")
        estado, _ = peticion(
            f"{WEB}/nutriologo/pacientes/{paciente_id}",
            "POST",
            {
                "_token": tok,
                "_method": "PUT",
                "nombre": nombre_pac + "Editado",
                "apellidos": "Persistencia",
                "fecha_nacimiento": "2017-03-15",
                "estado_paciente": "seguimiento",
            },
            {"Accept": "text/html"},
            opener,
            form=True,
        )
        filas = consultar("SELECT nombre, estado_paciente FROM pacientes WHERE id=%s", (paciente_id,))
        registrar("Web · editar paciente", bool(filas) and filas[0][0] == nombre_pac + "Editado",
                  f"HTTP {estado} bd={filas[0] if filas else None}")

    # REGISTRAR MEDICIONES
    if paciente_id:
        tok, _ = token_csrf(opener, "/nutriologo/evaluaciones/crear")
        estado, _ = peticion(
            f"{WEB}/nutriologo/evaluaciones",
            "POST",
            {
                "_token": tok,
                "paciente_id": str(paciente_id),
                "peso": "31.2",
                "talla": "132",
                "recomendaciones": f"Medición QA {SUFIJO}",
            },
            {"Accept": "text/html"},
            opener,
            form=True,
        )
        filas = consultar(
            "SELECT peso, talla, peso_kg, talla_cm, imc, nutriologo_id FROM evaluaciones "
            "WHERE paciente_id=%s ORDER BY id DESC LIMIT 1",
            (paciente_id,),
        )
        registrar("Web · registrar mediciones", bool(filas), f"HTTP {estado}")
        if filas:
            peso_kg, talla_cm, imc, nutri = filas[0][2], filas[0][3], filas[0][4], filas[0][5]
            registrar(
                "Web · mediciones guardan columnas numéricas que lee la app móvil",
                peso_kg is not None and talla_cm is not None and imc is not None,
                f"peso_kg={peso_kg} talla_cm={talla_cm} imc={imc}",
            )
            registrar("Web · la evaluación registra al nutriólogo autor", nutri is not None, f"nutriologo_id={nutri}")

    # CREAR y ACTUALIZAR plan alimenticio
    if paciente_id:
        tok, _ = token_csrf(opener, "/nutriologo/menus/crear")
        nombre_menu = f"MenuQA{SUFIJO}"
        estado, _ = peticion(
            f"{WEB}/nutriologo/menus",
            "POST",
            {
                "_token": tok,
                "paciente_id": str(paciente_id),
                "nombre": nombre_menu,
                "descripcion": "Plan de prueba",
                "estado": "activo",
            },
            {"Accept": "text/html"},
            opener,
            form=True,
        )
        filas = consultar("SELECT id FROM menus WHERE nombre=%s", (nombre_menu,))
        menu_id = filas[0][0] if filas else None
        registrar("Web · crear plan alimenticio", bool(menu_id), f"HTTP {estado} id={menu_id}")

        if menu_id:
            tok, _ = token_csrf(opener, f"/nutriologo/menus/{menu_id}/editar")
            estado, _ = peticion(
                f"{WEB}/nutriologo/menus/{menu_id}",
                "POST",
                {
                    "_token": tok,
                    "_method": "PUT",
                    "paciente_id": str(paciente_id),
                    "nombre": nombre_menu + "Actualizado",
                    "descripcion": "Plan actualizado",
                    "estado": "activo",
                },
                {"Accept": "text/html"},
                opener,
                form=True,
            )
            filas = consultar("SELECT nombre FROM menus WHERE id=%s", (menu_id,))
            registrar("Web · actualizar plan alimenticio", bool(filas) and filas[0][0].endswith("Actualizado"),
                      f"HTTP {estado} bd={filas[0][0] if filas else None}")

    return paciente_id


def elementos_de(datos):
    """Extrae la lista de resultados de una respuesta paginada de la API."""
    if not isinstance(datos, dict):
        return []
    for clave in ("data", "items", "results"):
        if isinstance(datos.get(clave), list):
            return datos[clave]
    return []


def verificar_ida_y_vuelta(auth):
    """
    Recorre el camino completo entre los dos clientes:
    el padre registra al niño desde la app (API) y el nutriólogo abre su
    expediente en la web, registra una medición y un plan; ambos deben volver
    a ser visibles desde la app.
    """
    print("\n=== VISIBILIDAD CRUZADA WEB <-> MOVIL ===", flush=True)

    if not auth:
        registrar("Visibilidad cruzada", False, "sin token de API")
        return None, None

    # 1. Padre + niño creados desde la API, que es lo que hace la app móvil.
    email_padre = f"qa.padre.{SUFIJO}@qanutrikids.com"
    estado, cuerpo = peticion(
        f"{API}/api/v1/usuarios",
        "POST",
        {
            "nombre": "QaPadre",
            "apellido_paterno": "Cruzado",
            "email": email_padre,
            "contrasena": "Passw0rd*2026",
            "rol": "padre",
        },
        auth,
    )
    padre_id = (json_o_none(cuerpo) or {}).get("id_usuario")
    registrar("Móvil · registrar padre desde la API", bool(padre_id), f"HTTP {estado} id={padre_id}")
    if not padre_id:
        return None, None

    nombre_nino = f"NinoQA{SUFIJO}"
    estado, cuerpo = peticion(
        f"{API}/api/v1/ninos",
        "POST",
        {
            "padre_id": padre_id,
            "nombre": nombre_nino,
            "apellidos": "Cruzado",
            "fecha_nacimiento": "2016-05-10",
            "sexo": "femenino",
        },
        auth,
    )
    nino_id = (json_o_none(cuerpo) or {}).get("id")
    registrar("Móvil · registrar niño desde la API", estado == 201 and bool(nino_id), f"HTTP {estado} id={nino_id}")
    if not nino_id:
        return padre_id, None

    filas = consultar("SELECT nombre, padre_id FROM ninos WHERE id=%s", (nino_id,))
    registrar("Móvil · el niño queda en PostgreSQL", bool(filas) and filas[0][1] == padre_id,
              f"bd={filas[0] if filas else None}")

    # 2. El nutriólogo abre el expediente en la web y lo vincula a ese niño.
    opener, ok_login, _ = sesion_web("nutriologo@nutrikids.com", "Nutri123*")
    if not ok_login:
        registrar("Web · el nutriólogo abre el expediente del niño", False, "no se pudo iniciar sesión")
        return padre_id, nino_id

    tok, formulario = token_csrf(opener, "/nutriologo/pacientes/crear")
    registrar(
        "Web · el formulario ofrece vincular con niños de la app",
        f'value="{nino_id}"' in formulario,
        "selector de vinculación presente",
    )

    estado, _ = peticion(
        f"{WEB}/nutriologo/pacientes",
        "POST",
        {
            "_token": tok,
            "nombre": nombre_nino,
            "apellidos": "Cruzado",
            "fecha_nacimiento": "2016-05-10",
            "nino_id": str(nino_id),
        },
        {"Accept": "text/html"},
        opener,
        form=True,
    )
    filas = consultar("SELECT id FROM pacientes WHERE nino_id=%s", (nino_id,))
    paciente_id = filas[0][0] if filas else None
    registrar("Web · expediente creado y vinculado al niño", bool(paciente_id), f"HTTP {estado} id={paciente_id}")
    if not paciente_id:
        return padre_id, nino_id

    # 3. Medición registrada en la web.
    tok, _ = token_csrf(opener, "/nutriologo/evaluaciones/crear")
    peticion(
        f"{WEB}/nutriologo/evaluaciones",
        "POST",
        {
            "_token": tok,
            "paciente_id": str(paciente_id),
            "peso": "28.4",
            "talla": "127",
            "recomendaciones": f"Cruzado {SUFIJO}",
        },
        {"Accept": "text/html"},
        opener,
        form=True,
    )
    filas = consultar(
        "SELECT nino_id, imc FROM evaluaciones WHERE paciente_id=%s ORDER BY id DESC LIMIT 1", (paciente_id,)
    )
    registrar(
        "Web · la medición hereda el nino_id del expediente",
        bool(filas) and filas[0][0] == nino_id,
        f"nino_id={filas[0][0] if filas else None} imc={filas[0][1] if filas else None}",
    )

    # 4. La app del padre debe ver esa medición.
    estado, cuerpo = peticion(f"{API}/api/v1/evaluaciones?nino_id={nino_id}", cabeceras=auth)
    items = elementos_de(json_o_none(cuerpo))
    registrar(
        "Móvil · la medición del nutriólogo se ve desde la app",
        estado == 200 and len(items) >= 1,
        f"HTTP {estado} evaluaciones={len(items)} imc={items[0].get('imc') if items else None}",
    )

    # 5. Plan alimenticio creado en la web.
    tok, _ = token_csrf(opener, "/nutriologo/menus/crear")
    nombre_menu = f"MenuCruzado{SUFIJO}"
    peticion(
        f"{WEB}/nutriologo/menus",
        "POST",
        {
            "_token": tok,
            "paciente_id": str(paciente_id),
            "nombre": nombre_menu,
            "descripcion": "Plan visible desde la app",
            "estado": "activo",
        },
        {"Accept": "text/html"},
        opener,
        form=True,
    )
    estado, cuerpo = peticion(f"{API}/api/v1/menus?nino_id={nino_id}", cabeceras=auth)
    items = elementos_de(json_o_none(cuerpo))
    registrar(
        "Móvil · el plan alimenticio del nutriólogo se ve desde la app",
        estado == 200 and any(i.get("nombre") == nombre_menu for i in items),
        f"HTTP {estado} planes={len(items)}",
    )

    # 6. Y al revés: lo que cambia el padre en la app se ve en el panel web.
    peticion(f"{API}/api/v1/ninos/{nino_id}", "PUT", {"peso_actual_kg": 29.1}, auth)
    _, listado = peticion(
        f"{WEB}/nutriologo/pacientes?q={nombre_nino}", cabeceras={"Accept": "text/html"}, opener=opener
    )
    registrar(
        "Web · el niño registrado en la app aparece en el panel del nutriólogo",
        nombre_nino in listado,
        "listado de pacientes",
    )
    filas = consultar("SELECT peso_actual_kg FROM ninos WHERE id=%s", (nino_id,))
    registrar("Móvil · la edición desde la app persiste en PostgreSQL", bool(filas) and filas[0][0] is not None,
              f"peso_actual_kg={filas[0][0] if filas else None}")

    return padre_id, nino_id


def verificar_modulos_restantes(auth, padre_id, nino_id):
    """
    Cubre los módulos que no entran en el flujo principal: roles, consultas,
    actividades (hábitos), recompensas y notificaciones (alertas).
    """
    print("\n=== MÓDULOS RESTANTES ===", flush=True)

    if not auth or not nino_id:
        registrar("Módulos restantes", False, "faltan datos previos")
        return

    # --- ROLES (sólo panel web) ---
    opener, ok_login, _ = sesion_web("admin@nutrikids.com", "Admin123*")
    if ok_login:
        filas = consultar("SELECT id, nombre FROM roles ORDER BY id LIMIT 1")
        if filas:
            rol_id, rol_nombre = filas[0]
            tok, pagina = token_csrf(opener, "/admin/roles")
            registrar("Roles · el catálogo se lee de PostgreSQL", rol_nombre in pagina, f"rol «{rol_nombre}»")

            descripcion = f"Descripcion QA {SUFIJO}"
            estado, _ = peticion(
                f"{WEB}/admin/roles/{rol_id}",
                "POST",
                {"_token": tok, "_method": "PUT", "descripcion": descripcion},
                {"Accept": "text/html"},
                opener,
                form=True,
            )
            guardado = consultar("SELECT descripcion FROM roles WHERE id=%s", (rol_id,))
            registrar("Roles · actualizar descripción persiste", bool(guardado) and guardado[0][0] == descripcion,
                      f"HTTP {estado} bd={guardado[0][0] if guardado else None}")

        # --- CONTACTOS: responder dejaba de guardarse ---
        peticion(
            f"{API}/api/v1/contactos",
            "POST",
            {"nombre": "QaContacto", "apellido": "Persistencia", "email": f"qa.contacto.{SUFIJO}@qanutrikids.com",
             "mensaje": f"Mensaje de prueba {SUFIJO}"},
        )
        filas = consultar(
            "SELECT id_contacto FROM contactos WHERE email=%s", (f"qa.contacto.{SUFIJO}@qanutrikids.com",)
        )
        contacto_id = filas[0][0] if filas else None
        registrar("Contactos · el mensaje del formulario llega a PostgreSQL", bool(contacto_id), f"id={contacto_id}")

        if contacto_id:
            tok, _ = token_csrf(opener, "/admin/contenido")
            respuesta = f"Respuesta de seguimiento QA {SUFIJO}"
            estado, _ = peticion(
                f"{WEB}/admin/contenido/contactos/{contacto_id}/responder",
                "POST",
                {"_token": tok, "respuesta": respuesta},
                {"Accept": "application/json", "X-Requested-With": "XMLHttpRequest"},
                opener,
                form=True,
            )
            filas = consultar(
                "SELECT respuesta, respondido_en FROM contactos WHERE id_contacto=%s", (contacto_id,)
            )
            registrar(
                "Contactos · la respuesta se guarda (antes fingía éxito)",
                bool(filas) and filas[0][0] == respuesta and filas[0][1] is not None,
                f"HTTP {estado} bd={filas[0][0] if filas else None}",
            )

    # --- CONSULTAS (citas) ---
    estado, cuerpo = peticion(
        f"{API}/api/v1/citas",
        "POST",
        {
            "id_padre": padre_id,
            "nino_id": nino_id,
            "fecha_preferida": "2026-09-15",
            "franja": "manana",
            "mensaje": f"Consulta QA {SUFIJO}",
        },
        auth,
    )
    cita_id = (json_o_none(cuerpo) or {}).get("id")
    registrar("Consultas · solicitar cita desde la app", estado == 201 and bool(cita_id), f"HTTP {estado} id={cita_id}")

    if cita_id:
        estado, _ = peticion(f"{API}/api/v1/citas/{cita_id}/estado", "PATCH", {"estado": "confirmada"}, auth)
        filas = consultar("SELECT estado FROM citas WHERE id=%s", (cita_id,))
        registrar("Consultas · cambiar estado persiste", bool(filas) and filas[0][0] == "confirmada",
                  f"HTTP {estado} bd={filas[0][0] if filas else None}")

    # --- ACTIVIDADES (hábitos) ---
    estado, cuerpo = peticion(f"{API}/api/v1/habitos-catalogo", cabeceras=auth)
    catalogo = elementos_de(json_o_none(cuerpo))
    registrar("Actividades · el catálogo de hábitos se lee de PostgreSQL", estado == 200 and len(catalogo) > 0,
              f"HTTP {estado} hábitos={len(catalogo)}")

    if catalogo:
        habito_id = catalogo[0].get("id")
        estado, cuerpo = peticion(
            f"{API}/api/v1/ninos/{nino_id}/habitos", "POST", {"habito_id": habito_id, "frecuencia": "diaria"}, auth
        )
        nino_habito_id = (json_o_none(cuerpo) or {}).get("id")
        registrar("Actividades · asignar hábito al niño", estado == 201 and bool(nino_habito_id),
                  f"HTTP {estado} id={nino_habito_id}")

        if nino_habito_id:
            estado, _ = peticion(
                f"{API}/api/v1/ninos/{nino_id}/habitos/{nino_habito_id}/registrar",
                "POST",
                {"completado": True},
                auth,
            )
            filas = consultar(
                "SELECT count(*) FROM habito_registros WHERE nino_habito_id=%s", (nino_habito_id,)
            )
            registrar("Actividades · registrar cumplimiento persiste", filas[0][0] >= 1,
                      f"HTTP {estado} registros={filas[0][0]}")

            estado, cuerpo = peticion(f"{API}/api/v1/ninos/{nino_id}/puntos", cabeceras=auth)
            registrar("Actividades · los puntos del niño se leen de la API", estado == 200, f"HTTP {estado}")

    # --- RECOMPENSAS ---
    estado, cuerpo = peticion(f"{API}/api/v1/recompensas-catalogo", cabeceras=auth)
    recompensas = elementos_de(json_o_none(cuerpo))
    registrar("Recompensas · el catálogo se lee de PostgreSQL", estado == 200 and len(recompensas) > 0,
              f"HTTP {estado} recompensas={len(recompensas)}")

    estado, cuerpo = peticion(f"{API}/api/v1/logros-catalogo", cabeceras=auth)
    registrar("Recompensas · el catálogo de logros se lee de PostgreSQL",
              estado == 200 and len(elementos_de(json_o_none(cuerpo))) > 0, f"HTTP {estado}")

    # --- NOTIFICACIONES (alertas clínicas) ---
    estado, cuerpo = peticion(
        f"{API}/api/v1/alertas",
        "POST",
        {"nino_id": nino_id, "tipo": "seguimiento", "severidad": "info", "mensaje": f"Alerta QA {SUFIJO}"},
        auth,
    )
    alerta_id = (json_o_none(cuerpo) or {}).get("id")
    registrar("Notificaciones · crear alerta", estado == 201 and bool(alerta_id), f"HTTP {estado} id={alerta_id}")

    if alerta_id:
        estado, _ = peticion(f"{API}/api/v1/alertas/{alerta_id}/atender", "POST", None, auth)
        filas = consultar("SELECT atendida FROM alertas WHERE id=%s", (alerta_id,))
        registrar("Notificaciones · marcar como atendida persiste", bool(filas) and filas[0][0] is True,
                  f"HTTP {estado} atendida={filas[0][0] if filas else None}")


PANTALLAS_ADMIN = [
    "/admin/usuarios",
    "/admin/usuarios/crear",
    "/admin/nutriologos",
    "/admin/nutriologos/crear",
    "/admin/roles",
    "/admin/instituciones",
    "/admin/configuracion",
    "/admin/contenido",
    "/admin/contenido/menus",
]

PANTALLAS_NUTRIOLOGO = [
    "/nutriologo/pacientes",
    "/nutriologo/pacientes/crear",
    "/nutriologo/evaluaciones",
    "/nutriologo/evaluaciones/crear",
    "/nutriologo/menus",
    "/nutriologo/menus/crear",
]


def verificar_pantallas():
    """Ninguna pantalla del panel debe romperse: todas responden 200 con HTML."""
    print("\n=== PANTALLAS DEL PANEL ===", flush=True)

    for etiqueta, credenciales, rutas in (
        ("admin", ("admin@nutrikids.com", "Admin123*"), PANTALLAS_ADMIN),
        ("nutriólogo", ("nutriologo@nutrikids.com", "Nutri123*"), PANTALLAS_NUTRIOLOGO),
    ):
        opener, ok_login, _ = sesion_web(*credenciales)
        if not ok_login:
            registrar(f"Pantallas de {etiqueta}", False, "no se pudo iniciar sesión")
            continue

        rotas = []
        for ruta in rutas:
            estado, cuerpo = peticion(f"{WEB}{ruta}", cabeceras={"Accept": "text/html"}, opener=opener)
            if estado != 200 or "<html" not in cuerpo.lower():
                rotas.append(f"{ruta} (HTTP {estado})")

        registrar(
            f"Pantallas de {etiqueta} responden correctamente",
            not rotas,
            f"{len(rutas) - len(rotas)}/{len(rutas)} ok",
        )
        for rota in rotas:
            print(f"       -> {rota}", flush=True)


def verificar_coherencia_global():
    """Invariantes que deben cumplirse para toda la base, no sólo los datos de prueba."""
    print("\n=== COHERENCIA GLOBAL DE LOS DATOS ===", flush=True)

    # Coherencia global: ninguna medición debe quedar sin normalizar
    filas = consultar(
        "SELECT count(*) FROM evaluaciones WHERE peso IS NOT NULL AND peso <> '' AND peso_kg IS NULL"
    )
    registrar("Ninguna evaluación queda sin columnas numéricas", filas[0][0] == 0, f"pendientes={filas[0][0]}")

    filas = consultar("SELECT count(*) FROM usuarios WHERE rol_id IS NULL")
    registrar("Ningún usuario sin rol_id", filas[0][0] == 0, f"pendientes={filas[0][0]}")

    filas = consultar(
        "SELECT count(*) FROM usuarios u JOIN roles r ON r.id = u.rol_id WHERE u.rol <> r.nombre"
    )
    registrar("Ningún usuario con rol desincronizado", filas[0][0] == 0, f"desincronizados={filas[0][0]}")

    filas = consultar(
        "SELECT count(*) FROM evaluaciones e JOIN pacientes p ON p.id = e.paciente_id "
        "WHERE p.nino_id IS NOT NULL AND e.nino_id IS NULL"
    )
    registrar("Ninguna medición de un expediente vinculado queda invisible para la app",
              filas[0][0] == 0, f"pendientes={filas[0][0]}")

    filas = consultar(
        "SELECT count(*) FROM menus m JOIN pacientes p ON p.id = m.paciente_id "
        "WHERE p.nino_id IS NOT NULL AND m.nino_id IS NULL"
    )
    registrar("Ningún plan de un expediente vinculado queda invisible para la app",
              filas[0][0] == 0, f"pendientes={filas[0][0]}")


def limpiar(paciente_id, nino_id):
    print("\n=== LIMPIEZA DE DATOS DE PRUEBA ===", flush=True)
    with conectar_bd() as cn, cn.cursor() as cur:
        for pid in filter(None, [paciente_id]):
            cur.execute("DELETE FROM menus WHERE paciente_id=%s", (pid,))
            cur.execute("DELETE FROM evaluaciones WHERE paciente_id=%s", (pid,))
            cur.execute("DELETE FROM pacientes WHERE id=%s", (pid,))

        if nino_id:
            cur.execute("DELETE FROM menus WHERE nino_id=%s", (nino_id,))
            cur.execute("DELETE FROM evaluaciones WHERE nino_id=%s", (nino_id,))
            cur.execute("DELETE FROM pacientes WHERE nino_id=%s", (nino_id,))
            # Los registros de hábitos cuelgan de nino_habitos, así que van primero.
            cur.execute(
                "DELETE FROM habito_registros WHERE nino_habito_id IN "
                "(SELECT id FROM nino_habitos WHERE nino_id=%s)",
                (nino_id,),
            )
            for tabla in ("nino_habitos", "nino_logros", "nino_puntos", "nino_recompensas",
                          "nino_retos", "nino_credenciales", "alergias", "alertas",
                          "notas_nutriologo", "reportes", "citas"):
                cur.execute(f"DELETE FROM {tabla} WHERE nino_id=%s", (nino_id,))
            cur.execute("DELETE FROM ninos WHERE id=%s", (nino_id,))

        cur.execute("DELETE FROM usuarios WHERE email LIKE %s", ("%@qanutrikids.com",))
        borrados = cur.rowcount
        cur.execute("DELETE FROM instituciones WHERE nombre LIKE %s", ("Escuela QA %",))
        cur.execute("DELETE FROM contactos WHERE email LIKE %s", ("%@qanutrikids.com",))
        cn.commit()
    print(f"Usuarios de prueba eliminados: {borrados}", flush=True)


def main():
    print(f"NutriKids · verificación de persistencia (sufijo {SUFIJO})")
    print(f"API={API}  WEB={WEB}")

    auth = verificar_api()
    verificar_web()
    paciente_id = verificar_nutriologo()
    padre_id, nino_id = verificar_ida_y_vuelta(auth)
    verificar_modulos_restantes(auth, padre_id, nino_id)
    verificar_pantallas()
    verificar_coherencia_global()
    limpiar(paciente_id, nino_id)

    total = len(resultados)
    fallos = [r for r in resultados if not r[1]]
    print("\n" + "=" * 70)
    print(f"RESULTADO: {total - len(fallos)}/{total} comprobaciones correctas")
    for nombre, _, detalle in fallos:
        print(f"  FALLA: {nombre} — {detalle}")
    print("=" * 70)

    raise SystemExit(1 if fallos else 0)


if __name__ == "__main__":
    main()
