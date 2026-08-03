# 01 — Auditoría Técnica del Proyecto NutriKids (Estado Actual)

> **Rol del documento**: fuente de verdad sobre lo que **realmente existe** en el repositorio a fecha 2026-07-28. Todo diseño posterior (02 en adelante) parte de estos hechos verificados, no de suposiciones. Ningún agente debe re-auditar el repositorio completo sin antes leer este documento.

---

## 1. Metodología de la auditoría

Se inspeccionó directamente el código fuente (no la documentación previa del propio repo, que puede estar desactualizada): `composer.json`, `package.json`, `docker-compose.yml`, Dockerfiles, `routes/web.php`, todos los `Controllers`, `Models`, `Middleware`, las 22 migraciones de Laravel una por una, `fastapi/models.py` (SQLAlchemy), `fastapi/main.py`, `fastapi/security.py`, `fastapi/config.py`, `flask/requirements.txt` y `README_SECURITY.md` (documento propio del equipo, tratado como declaración de intención, no como hecho verificado por sí solo).

---

## 2. Stack tecnológico verificado

| Componente | Tecnología | Versión | Evidencia |
|---|---|---|---|
| Backend admin/nutriólogo | PHP + Laravel | PHP ^8.2, Laravel ^12.0 | `composer.json` |
| API central | Python + FastAPI | fastapi 0.115.0, uvicorn 0.30.6 | `fastapi/requirements.txt` |
| Portal público padres | Python + Flask | Flask 3.0.3 | `flask/requirements.txt` |
| ORM Laravel | Eloquent | — | `app/Models/*.php` |
| ORM FastAPI | SQLAlchemy 2.0 (declarativo, `Mapped`) | 2.0.36 | `fastapi/models.py` |
| Auth API | JWT (python-jose) + bcrypt | jose 3.3.0 | `fastapi/security.py` |
| Auth Laravel | Sesión (`Auth::login`) + `password_verify` manual | — | `AuthController.php` |
| Base de datos | PostgreSQL | 15-alpine | `docker-compose.yml` |
| Frontend build | Vite + TailwindCSS | Vite 7.0.7, Tailwind 4.0 | `package.json` |
| PDF | barryvdh/laravel-dompdf | ^3.1 | `composer.json` |
| Contenedores | Docker Compose, 4 servicios | — | `docker-compose.yml` |

**Conclusión**: no hay un único framework — hay **tres aplicaciones independientes que comparten una base de datos PostgreSQL**. No existe todavía app móvil, cache (Redis), balanceador, monitoreo, ni CI/CD.

---

## 3. Arquitectura actual (as-is)

```
                     ┌────────────────────┐
   navegador ───────▶│   Laravel  :8080   │── sesión (cookie) ── Postgres (Eloquent, escribe)
   (admin/nutri)     │  Blade + RBAC roles │
                     └─────────┬──────────┘
                               │ HTTP interno (NUTRIKIDS_API_BASE_URL)
                               ▼
                     ┌────────────────────┐
                     │   FastAPI  :8000   │── JWT ── Postgres (SQLAlchemy, lee/escribe)
                     │  API REST /api/*    │
                     └─────────▲──────────┘
                               │ HTTP (NUTRIKIDS_API_BASE_URL)
                     ┌─────────┴──────────┐
   navegador ───────▶│   Flask   :5000    │── cookie sesión propia
   (padres público)  │  Jinja + flask-login│
                     └────────────────────┘

                     PostgreSQL :5432 — compartida, con DDL propiedad de Laravel (migrations)
```

**Puntos clave verificados en `routes/web.php`**:
- Laravel es el *hub* de entrada. Si `FLASK_PUBLIC_URL` está definida, todas las rutas públicas (`/`, `/login`, `/Obesidad`, `/calculadora`, `/Foros`, `/Contacto`, `/nutriologos`, `/Comentarios`, `/conocenos`, `/dashboard`) se **redirigen** (HTTP redirect) hacia Flask. Laravel conserva únicamente `/admin/*` y `/nutriologo/*` protegidas por `role:` middleware.
- El login (`POST /IniciarSesion`) se procesa **en Laravel**, no en FastAPI, aunque Flask (otro origen/puerto) es quien renderiza el formulario — de ahí el middleware a medida `CorsAllowFlask`.
- Tras login exitoso, Laravel decide a dónde redirigir según el `rol` guardado en la tabla `usuarios`.

---

## 4. Modelo de datos verificado (hechos, no diseño)

### 4.1 Tablas con esquema real

| Tabla | Columnas relevantes | Observación |
|---|---|---|
| `usuarios` | nombre, apellido_paterno, apellido_materno, email, contrasena, rol | `rol` es string libre, no FK a tabla de roles. Ver `RoleMiddleware`. |
| `contactos` | nombre, apellido, email, mensaje, fecha_creacion | Formulario público. |
| `comentarios` | id_usuario (FK), nombre, apellido, comentario, fecha_comentario | |
| `discusiones` | id_usuario (FK), tema, descripcion, fecha_creacion | Foro. |
| `pacientes` | nombre, apellidos, fecha_nacimiento | **Sin FK a `usuarios` (padre) ni a nutriólogo.** |
| `evaluaciones` | paciente_id (FK), peso (string), talla (string), recomendaciones | `peso`/`talla` como `string`, no numérico. |
| `menus` | nombre, paciente_id (FK), descripcion | |
| `reportes` | paciente_id (FK), titulo, contenido | |
| `citas` | id_padre (FK usuarios), id_nutriologo (FK usuarios, nullable), fecha_preferida, franja, telefono, mensaje, estado | Añadida en migración posterior (`2026_03_29`); es la única entidad "clínica" con esquema completo y relaciones correctas. |

### 4.2 ⚠️ Tablas "cáscara" (hallazgo crítico)

Las siguientes tablas **existen físicamente pero solo tienen `id` + `created_at`/`updated_at`**, tanto en la migración Laravel como en el modelo SQLAlchemy espejo de FastAPI. **No almacenan ningún dato de negocio todavía**:

| Tabla | Modelo Laravel | Modelo FastAPI | Migración |
|---|---|---|---|
| `infantes` | `Infante.php` | `Infante` | `2025_11_24_021616_create_infantes_table.php` |
| `alertas` | `Alerta.php` | `Alerta` | `2025_11_25_155124_create_alertas_table.php` |
| `alergias` | `Alergia.php` | `Alergia` | `2025_11_25_155127_create_alergias_table.php` |
| `notas_nutriologo` | `NotaNutriologo.php` | `NotaNutriologo` | `2025_11_25_155130_create_notas_nutriologo_table.php` |
| `menus_semanales` | `MenuSemanal.php` | `MenuSemanal` | `2025_11_25_155133_create_menus_semanales_table.php` |

**Interpretación**: es scaffolding intencional dejado por el equipo anterior como reserva de nombres de tabla ("reservadas para futuras columnas de negocio", literal en el comentario de `fastapi/models.py:143`), no un bug de datos perdidos. **Ningún dato real de alergias, alertas, notas clínicas, menús semanales o "infante" existe hoy**. El documento `03_BaseDatos.md` diseña el esquema real que debe reemplazar estas cáscaras.

### 4.3 Duplicidad conceptual `Paciente` vs `Infante`

Existen dos modelos que aparentan representar al mismo sujeto (el niño/paciente): `Paciente` (con nombre/apellidos/fecha_nacimiento, usado por `evaluaciones`, `menus`, `reportes`) e `Infante` (vacío, sin uso en ningún controlador ni router). **No hay evidencia de que `Infante` se use en ningún flujo actual** (no aparece en `routes/web.php`, ni en `fastapi/routers/*`, salvo el `InfanteController.php` de Laravel, que está **vacío** — ver 4.4). Se interpreta como un intento abandonado de modelar al niño por separado del expediente clínico. El diseño objetivo (`03_BaseDatos.md`) resuelve esta ambigüedad consolidando el concepto en una única entidad `Nino` vinculada explícitamente a `usuarios` (padre) y a `usuarios` (nutriólogo asignado).

### 4.4 Controlador vacío

`app/Http/Controllers/InfanteController.php` pesa 1 byte y no contiene ni siquiera `<?php`. Es un archivo huérfano, no registrado en `routes/web.php`.

---

## 5. Autenticación — duplicación de lógica (hallazgo crítico)

Existen **dos implementaciones independientes de verificación de contraseña contra la misma tabla `usuarios`**:

1. **Laravel** (`AuthController.php:28-31`): `User::where('email', ...)->first()` + `password_verify($request->contrasena, $hash)` manual (no usa el `Hash` facade de Laravel ni el `Authenticatable` estándar de login por credenciales), luego `Auth::login($user)` para crear sesión.
2. **FastAPI** (`security.py` + `routers/auth.py`): `bcrypt.checkpw(...)` + emisión de JWT.

Ambas leen el mismo campo `contrasena` (bcrypt) de la misma tabla, pero son **dos superficies de autenticación separadas y no sincronizadas**: un usuario autenticado en Laravel (cookie de sesión) no tiene automáticamente un JWT de FastAPI, y viceversa. Esto ya obliga a Flask a re-loguearse contra Laravel vía `CorsAllowFlask` + `/IniciarSesion`. Es una fuente de riesgo (dos lugares donde se puede introducir un bug de autenticación) y de fricción para la futura app móvil, que necesitará JWT y no cookies de sesión PHP.

---

## 6. Seguridad — estado real

`README_SECURITY.md` (propio del equipo) documenta trabajo real y verificable:

- Rate limiting **en memoria** (no Redis) en `fastapi/deps.py` para `/api/auth/login` (5/min), `/api/auth/register` (3/min), `/api/contactos` (3/min).
- RBAC por dependencia `require_roles(...)` en endpoints sensibles de FastAPI (pacientes, evaluaciones, menús, reportes, citas).
- Cabeceras de seguridad HTTP (`X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`, `Referrer-Policy`) inyectadas en FastAPI y Flask.
- CORS restringido por lista blanca de orígenes en FastAPI y middleware a medida `CorsAllowFlask` en Laravel.
- Cookies de sesión Flask con `HttpOnly` y `SameSite=Lax`.

**Gaps verificados** (no cubiertos por lo anterior):
- El rate limiter en memoria **no sobrevive reinicios ni funciona con más de un worker/réplica** — no sirve en un despliegue balanceado horizontalmente (ver `05_Seguridad.md` y `09_Cloud.md`).
- PostgreSQL expuesto al host (`"5432:5432"` en `docker-compose.yml`) pese a que el propio `README_SECURITY.md` lo califica de "protegido, interno en Docker" — inconsistencia entre intención y configuración real.
- Secretos de desarrollo hardcodeados directamente en `docker-compose.yml` (`NUTRIKIDS_SECRET_KEY`, `FLASK_SECRET_KEY`, password de Postgres en texto plano) — aceptables solo en desarrollo local, nunca en producción.
- No hay refresh tokens: `create_access_token` en `security.py` emite un único JWT de 120 minutos, sin rotación ni revocación.
- No hay `2FA`, ni auditoría de intentos fallidos persistente, ni bloqueo progresivo de cuentas.
- No hay HTTPS/TLS en ningún punto de la configuración local (correcto para desarrollo, pendiente para producción).

---

## 7. Configuración de entorno — inconsistencias verificadas

- Coexisten en la raíz: `.env`, `.env.example`, `.env.laravel`, `.env.flask`, y dentro de `flask/`: otro `.env` y `.env.example` propios.
- `.env.example` (raíz) define `FLASK_PUBLIC_URL` **dos veces** (línea 51 con puerto `5001`, línea 73 con `127.0.0.1:5001`), mientras que `docker-compose.yml` usa realmente el puerto **5000** para Flask. Un desarrollador que siga literalmente `.env.example` configurará mal el entorno.
- `.env` no está versionado (correctamente excluido en `.gitignore`); los ficheros `.env.laravel`/`.env.flask` sí están versionados como plantillas y solo contienen valores de desarrollo — correcto, pero a vigilar en cada PR.

---

## 8. Carpetas y artefactos residuales

- `web1_flask/` contiene únicamente `templates/`, sin `app.py` ni lógica — resto de una refactorización, no forma parte del flujo activo (`docker-compose.yml` monta `./flask`, no `./web1_flask`).
- `CREDENCIALES_TEMPORALES.md` y `README_LARAVEL.md` aparecen eliminados en el working tree actual (`git status` al inicio de esta sesión) — no se analizan por no ser ya parte del proyecto.

---

## 9. Resumen de hallazgos priorizados

| # | Hallazgo | Severidad | Documento que lo resuelve |
|---|---|---|---|
| 1 | Autenticación duplicada Laravel/FastAPI sin fuente única | Alta | `02_Arquitectura.md`, `14_DecisionesArquitectura.md` |
| 2 | Tablas cáscara sin esquema real (infantes, alertas, alergias, notas, menús semanales) | Alta | `03_BaseDatos.md` |
| 3 | `Paciente` sin FK a padre/nutriólogo; duplicidad con `Infante` | Alta | `03_BaseDatos.md` |
| 4 | Rate limiting en memoria, no apto para múltiples réplicas | Media | `05_Seguridad.md`, `08_Docker.md` |
| 5 | Postgres expuesto al host en compose | Media | `08_Docker.md`, `05_Seguridad.md` |
| 6 | Secretos hardcodeados en compose | Media | `05_Seguridad.md` |
| 7 | Sin refresh tokens / revocación de JWT | Media | `05_Seguridad.md` |
| 8 | `.env.example` inconsistente (puerto Flask duplicado/erróneo) | Baja | `16_PlanModernizacion.md` |
| 9 | `InfanteController.php` vacío, huérfano | Baja | `16_PlanModernizacion.md` |
| 10 | Carpeta `web1_flask/` residual | Baja | `16_PlanModernizacion.md` |
| 11 | Sin monitoreo, sin CI/CD, sin balanceador, sin cache | Alta (para escala futura) | `09_Cloud.md`, `11_Deployment.md` |
| 12 | Sin app móvil | — (es el objetivo, no un defecto) | `07_AppMovil.md` |

Este documento **no se debe reescribir** salvo que el código fuente cambie sustancialmente; en ese caso se actualiza aquí y se referencia la fecha de la nueva auditoría en `Bitacora.md`.
