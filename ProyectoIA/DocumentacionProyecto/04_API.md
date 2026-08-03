# 04 — Diseño de la API REST Central

> Depende de: [`02_Arquitectura.md`](./02_Arquitectura.md) (FastAPI = única fuente de negocio), [`03_BaseDatos.md`](./03_BaseDatos.md) (entidades). Detalle de auth en [`05_Seguridad.md`](./05_Seguridad.md).

---

## 1. Principios de contrato

1. **Un único backend de API**: FastAPI. Laravel y Flask/Next.js son clientes, no co-emisores de endpoints de negocio.
2. **Versionado explícito en la URL**: `/api/v1/...`. Hoy el prefijo es `/api/*` sin versión (verificado en `fastapi/main.py`) — se introduce `v1` desde el primer cambio de contrato para no romper clientes futuros (móvil) cuando cambie la forma de un recurso.
3. **JSON siempre**, `Content-Type: application/json`. Nunca se devuelve HTML desde la API.
4. **Contrato autodescriptivo**: se mantiene y se exige mantener actualizado el esquema OpenAPI que FastAPI genera automáticamente (`/docs`, `/redoc`, `/openapi.json` — ya presentes en `fastapi/main.py`), como fuente de verdad para generar clientes tipados (web y React Native).
5. **Idempotencia**: todo `PUT`/`DELETE` es idempotente por diseño; `POST` de creación no lo es salvo que se documente una clave de idempotencia (necesario en fase de mensajería/pagos futuros).

---

## 2. Estructura de recursos (`/api/v1/...`)

| Recurso | Roles con acceso | Descripción |
|---|---|---|
| `POST /auth/registro` | público (solo crea rol `padre`) | Rate limit estricto |
| `POST /auth/login` | público | Devuelve access + refresh token |
| `POST /auth/refresh` | público (requiere refresh token válido) | Rotación de refresh token |
| `POST /auth/logout` | autenticado | Revoca refresh token actual |
| `GET /usuarios`, `POST /usuarios` | admin | Gestión de cuentas adultas |
| `GET/POST/PUT /ninos` | padre (solo los suyos), nutriólogo (asignados), admin (todos) | Ver `05_Seguridad.md` §RBAC por fila |
| `POST /ninos/{id}/vincular-dispositivo` | padre | Genera `codigo_vinculacion` para la app móvil |
| `GET/POST/PUT /evaluaciones` | nutriólogo, admin | |
| `GET/POST/PUT /alergias` | nutriólogo, admin; lectura también padre (solo de sus hijos) | |
| `GET/POST /alertas`, `POST /alertas/{id}/atender` | nutriólogo, admin | |
| `GET/POST /notas-nutriologo` | nutriólogo, admin | `privada=true` nunca se expone al padre |
| `GET/POST/PUT /menus`, `/menus/{id}/items` | nutriólogo, admin; lectura padre/niño (solo asignados) | |
| `GET/POST /menus-semanales` | nutriólogo, admin | catálogo de plantillas |
| `GET/POST /reportes`, `GET /reportes/{id}/pdf` | nutriólogo, admin; lectura padre (solo de sus hijos) | PDF se sigue generando en Laravel a partir de datos leídos de este endpoint |
| `GET/POST /citas`, `PATCH /citas/{id}/asignar`, `/estado`, `/tomar` | según rol (idéntico a las reglas ya implementadas hoy en Laravel, migradas a la API) | |
| `GET/POST /comentarios`, `GET/POST/PUT/DELETE /discusiones` | lectura pública, escritura autenticada | ya existe hoy, se conserva |
| `POST /contactos` | público con rate limit | ya existe hoy |
| `GET /habitos-catalogo`, `GET/POST /ninos/{id}/habitos` | nutriólogo asigna; niño consume vía móvil | nuevo, gamificación |
| `POST /ninos/{id}/habitos/{id}/registrar` | niño (móvil) | marca hábito del día como completado |
| `GET /retos-catalogo`, `GET /ninos/{id}/retos` | niño (móvil), lectura padre | nuevo |
| `GET /logros-catalogo`, `GET /ninos/{id}/logros` | niño (móvil), lectura padre | nuevo |
| `GET /ninos/{id}/puntos` | niño (móvil), padre, nutriólogo | saldo agregado |
| `GET /recompensas-catalogo`, `POST /ninos/{id}/recompensas/{id}/canjear` | niño (móvil) | nuevo |
| `GET /health` | público | ya existe, se conserva tal cual |

---

## 3. Convenciones de request/response

### 3.1 Paginación
Todo listado (`GET` de colección) usa paginación por cursor u offset consistente:
```
GET /api/v1/ninos?page=1&per_page=20
```
```json
{
  "data": [ ... ],
  "meta": { "page": 1, "per_page": 20, "total": 134, "total_pages": 7 }
}
```
Ningún endpoint devuelve una colección sin paginar por defecto — mitiga el riesgo ya señalado en la auditoría de fuga masiva de datos (el caso histórico de `GET /api/contactos` sin RBAC, ya corregido, pero el patrón de "listar todo sin límite" debe evitarse estructuralmente).

### 3.2 Errores
Formato uniforme para todo error 4xx/5xx:
```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Errores de validación",
    "details": [ {"field": "email", "issue": "formato inválido"} ]
  }
}
```
Códigos HTTP usados de forma consistente: `400` validación, `401` no autenticado, `403` autenticado sin permiso, `404` no existe o no visible para el rol (se prefiere `404` sobre `403` cuando revelar la existencia del recurso ya es una fuga de información — p. ej. un padre consultando el `nino_id` de otra familia), `409` conflicto (p. ej. email duplicado), `422` semánticamente inválido, `429` rate limit, `5xx` error interno (nunca se expone stacktrace en producción — ya es el patrón usado hoy en `AuthController.php:77`, se generaliza a todos los endpoints).

### 3.3 Autenticación
`Authorization: Bearer <access_token>` en todo endpoint no público. Detalle completo del ciclo de vida del token en `05_Seguridad.md`.

### 3.4 Nombres de campos
Snake_case en JSON (consistente con el estilo ya usado en el dominio, p. ej. `id_usuario`, `fecha_nacimiento`), fechas en ISO-8601 UTC (`2026-07-28T10:00:00Z`).

---

## 4. Clientes de la API y cómo cada uno la consume

| Cliente | Forma de consumo | Nota |
|---|---|---|
| Laravel (admin/nutriólogo) | HTTP interno server-to-server con `Http::withToken(...)` de Laravel; el JWT del usuario logueado en Laravel se obtiene llamando `/auth/login` en el momento del login y se guarda en la sesión de Laravel (no se re-implementa la verificación de contraseña en PHP — resuelve el hallazgo #1 de la auditoría) | Ver ADR-002 en `14_DecisionesArquitectura.md` |
| Flask / Next.js (público) | Igual patrón: Flask deja de leer Postgres directo, todo pasa por la API | |
| App móvil (React Native) | Cliente HTTP directo (Axios/fetch) contra el gateway público, con manejo de refresh token en el cliente | Ver `07_AppMovil.md` §API necesaria |

---

## 5. Generación de contrato / SDKs

Se recomienda generar clientes TypeScript tipados (para Next.js y React Native) automáticamente desde el `openapi.json` que FastAPI ya expone (`openapi-typescript` o similar), evitando mantener tipos a mano en tres lugares. Es tarea de la fase de tooling, ver `13_Backlog.md`.

## 6. Qué NO hace la API

- No renderiza vistas ni sirve HTML/CSS/JS de aplicación (eso es responsabilidad de cada cliente).
- No conoce detalles de presentación de ningún cliente (p. ej. no decide colores de la app móvil, ni genera el PDF de reportes — eso permanece en Laravel/dompdf, consumiendo datos de `GET /reportes/{id}`).
- No implementa lógica duplicada de autenticación en otro servicio — es la única fuente (principio de `02_Arquitectura.md` §1).
