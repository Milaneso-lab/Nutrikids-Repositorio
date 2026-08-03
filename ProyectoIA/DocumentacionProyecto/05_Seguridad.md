# 05 — Estrategia de Seguridad Empresarial

> Depende de: [`01_AuditoriaProyecto.md`](./01_AuditoriaProyecto.md) §6 (gaps reales verificados), [`02_Arquitectura.md`](./02_Arquitectura.md), [`03_BaseDatos.md`](./03_BaseDatos.md) §3.3 (refresh_tokens).
> NutriKids trata **datos de salud infantil** (peso, talla, alergias, notas clínicas de menores) — el estándar de seguridad debe tratarse como dato sensible/PII de menores en todo el diseño, no solo como "una app más".

---

## 1. Autenticación

### 1.1 JWT de acceso
- Emitido únicamente por la API (FastAPI) — fin de la duplicación Laravel/FastAPI señalada en la auditoría.
- **Vida corta**: 15 minutos (se reduce de los 120 minutos actuales, verificados en `fastapi/config.py`). Un access token de vida larga es innecesario si existe refresh token, y amplía la ventana de daño si se filtra.
- Claims mínimos: `sub` (id usuario), `rol`, `exp`, `iat`, `jti` (identificador único del token, necesario para revocación selectiva).
- Firmado con **HS256** en fase actual (simétrico, ya usado); se evalúa migrar a **RS256** (asimétrico) cuando haya más de un servicio verificando tokens de forma independiente sin compartir el secreto (p. ej. un futuro servicio de mensajería) — documentado como decisión diferida en `14_DecisionesArquitectura.md`.

### 1.2 Refresh tokens
- No existen hoy (hallazgo de auditoría). Se implementan según `03_BaseDatos.md` §3.3: token opaco (no JWT) de vida larga (30 días web / 90 días móvil), guardado **hasheado** en `refresh_tokens`, nunca en claro en BD.
- **Rotación en cada uso**: al refrescar, el token usado se marca `revocado_en` y se emite uno nuevo. Si un refresh token ya revocado se reintenta usar, se interpreta como posible robo y se revocan **todos** los refresh tokens del usuario (respuesta a reuse-detection, patrón estándar OWASP).
- Logout = revocar el refresh token actual (`revocado_en = now()`), no solo borrar el token del cliente.

### 1.3 Hashing de contraseñas
- **bcrypt** (ya en uso en ambos lados, `password_verify`/`bcrypt.checkpw`) se mantiene como estándar único, con factor de costo ≥ 12 (ya configurado: `BCRYPT_ROUNDS=12` en `.env`). Se evalúa **Argon2id** para nuevas cuentas en fase de madurez (mejor resistencia a ASICs), sin migración forzada de hashes existentes (se re-hashea de forma perezosa en el próximo login exitoso).
- El PIN del niño (`nino_credenciales.pin_hash`, ver `03_BaseDatos.md` §3.5) usa el mismo esquema de hashing pero con política distinta (ver §7).

### 1.4 Verificación de email y recuperación de cuenta
- Registro de padre requiere verificación de email antes de acceder a funciones sensibles (agendar cita, ver expediente) — tabla `password_reset_tokens` ya existe en el esquema Laravel actual, se reutiliza el patrón para verificación de email.
- Recuperación de contraseña: token de un solo uso, vida corta (30 min), invalidado tras el primer uso.

---

## 2. Autorización (RBAC + control por fila)

- Roles normalizados (`roles`/`permisos`, `03_BaseDatos.md` §3.2), verificados en cada endpoint vía dependencia de FastAPI (patrón `require_roles(...)` ya existente y correcto, se generaliza).
- **Control por fila** (row-level), no solo por rol: un padre con rol correcto igualmente no puede leer `ninos` que no sean suyos; un nutriólogo no puede leer pacientes no asignados. Se implementa como filtro obligatorio en la capa de repositorio de la API (`WHERE padre_id = :usuario_actual` o `WHERE nutriologo_asignado_id = :usuario_actual`), nunca confiando en que el cliente envíe el filtro correcto.
- Principio de mínimo privilegio para servicio-a-servicio: si Laravel llama a la API en nombre de un admin, reenvía el JWT de ese admin (no un token de "super-servicio" con acceso total).

---

## 3. Transporte y red

- **HTTPS/TLS obligatorio en todo entorno que no sea `localhost`**, terminado en el gateway (Nginx/Traefik, `02_Arquitectura.md` §2). Certificados vía Let's Encrypt (autorenovables) en cloud propio, o gestionados por el proveedor si se usa PaaS (ver `09_Cloud.md`).
- HSTS habilitado (`Strict-Transport-Security`) una vez HTTPS esté estable en producción.
- **PostgreSQL y Redis nunca expuestos a Internet** — corrige el hallazgo de auditoría (`5432:5432` publicado en `docker-compose.yml` actual). Solo alcanzables desde la red privada/Docker network donde corre la API.
- Firewall de host/cloud (security groups) con regla por defecto **deny-all** entrante, abriendo únicamente 443 (y 80 para redirección a 443) al público; el resto de puertos (Postgres, Redis, API directa) solo accesibles desde la red interna o VPN de administración.

---

## 4. Rate limiting y protección anti-abuso

- Sustituye el rate limiter en memoria actual (`fastapi/deps.py`, no apto para múltiples réplicas — hallazgo de auditoría) por **contadores en Redis** (sliding window o token bucket), compartidos entre todas las réplicas de la API.
- Límites base (se conservan los ya definidos y verificados como correctos en el proyecto actual, ahora distribuidos): login 5/min/IP, registro 3/min/IP, contacto 3/min/IP; se añade límite global por IP a nivel de gateway (capa 7) como primera barrera antes de llegar a la API.
- Bloqueo progresivo de cuenta tras N intentos fallidos de login (p. ej. 5 intentos → bloqueo 15 min), registrado y auditado — no existe hoy.
- Protección específica para endpoints de canje de recompensas y registro de hábitos (app móvil) contra scripts que simulen actividad de niño para farmear puntos — regla de negocio (máximo un registro de hábito por día ya lo limita a nivel de constraint único en `habito_registros`, ver `03_BaseDatos.md` §6.3).

---

## 5. Gestión de secretos y variables de entorno

- **Ningún secreto hardcodeado en `docker-compose.yml` ni en el repositorio** — corrige el hallazgo de auditoría (`NUTRIKIDS_SECRET_KEY`, `FLASK_SECRET_KEY`, password de Postgres en texto plano en el compose actual).
- Secretos gestionados vía: `.env` no versionado en desarrollo local (ya es el patrón correcto hoy), y **gestor de secretos del proveedor cloud** (AWS Secrets Manager / Parameter Store, o equivalente) en producción — nunca variables de entorno en texto plano en el orquestador de producción.
- Rotación periódica de `NUTRIKIDS_SECRET_KEY` (secreto de firma JWT) planificada con estrategia de doble clave (aceptar la anterior durante una ventana de gracia) para no invalidar sesiones activas de golpe.
- Un único archivo `.env.example` canónico por servicio, consistente con `docker-compose.yml` (corrige la inconsistencia de puertos de Flask detectada en la auditoría, §7) — tarea de limpieza en `16_PlanModernizacion.md`.

---

## 6. Cabeceras HTTP y hardening web

Se conserva y generaliza lo ya implementado correctamente (verificado en `fastapi/main.py` y `flask/app.py`): `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`, `Content-Security-Policy` (se añade — no presente hoy, necesaria para mitigar XSS en el sitio público y el panel admin), cookies con `HttpOnly` + `SameSite=Lax` + `Secure` (en producción con HTTPS).

CSRF: Laravel ya provee protección CSRF nativa para sus formularios de backoffice — se mantiene. La API (JSON + Bearer token) no usa cookies de sesión para autenticación de negocio, por lo que no es susceptible a CSRF clásico en sus endpoints `/api/v1/*`.

---

## 7. Consideraciones específicas de datos de menores

- **Minimización de datos**: la cuenta del niño (`nino_credenciales`) no almacena email ni contraseña adulta — solo PIN corto y vínculo al dispositivo, según diseño de `03_BaseDatos.md` §3.5.
- **Consentimiento parental**: toda cuenta de niño se crea exclusivamente desde la cuenta del padre (`POST /ninos`, nunca autoregistro de un menor), y la vinculación del dispositivo móvil requiere un código generado por el padre (`codigo_vinculacion`).
- **Visibilidad de notas clínicas privadas**: `notas_nutriologo.privada = true` nunca se expone al padre ni al niño vía API — se aplica como filtro obligatorio, no opcional, en el endpoint.
- **Alineación con marcos de referencia**: aunque el proyecto no opera aún en jurisdicciones con obligación legal formal (COPPA/GDPR-K), el diseño se alinea preventivamente con sus principios (minimización, consentimiento parental explícito, no perfilado publicitario de menores) para evitar deuda de cumplimiento costosa de revertir después.

---

## 8. Monitoreo y logs de seguridad

- **Logs de auditoría de acceso** a datos clínicos sensibles (quién leyó/modificó el expediente de qué niño y cuándo) — tabla o almacén de logs separado del log operativo general, con retención más larga.
- Centralización de logs (Loki o equivalente) con alertas sobre: picos de `401`/`403`, ráfagas de `429`, intentos de login fallidos repetidos desde una misma IP/rango.
- Trazado de errores de aplicación con Sentry (o equivalente) en los tres backends, con scrubbing automático de PII antes de enviar el evento (nunca se loguea `contrasena`, `pin_hash`, tokens completos).
- Métricas de seguridad expuestas a Prometheus: tasa de `401`/`403`/`429` por endpoint, latencia de auth, tokens revocados/hora.

## 9. Respaldos y recuperación

- Backups automáticos diarios de PostgreSQL (dump lógico + snapshot de volumen si el proveedor cloud lo soporta), retención mínima 30 días, con al menos una copia fuera de la región primaria.
- Prueba de restauración periódica (trimestral) documentada — un backup nunca probado no cuenta como backup real.
- Plan de recuperación ante desastre (RPO/RTO objetivo) definido formalmente al entrar en producción real, documentado en `09_Cloud.md` §Continuidad.
- Cifrado de backups en reposo (el propio servicio de backup gestionado del proveedor cloud, o `gpg`/`age` si es autogestionado).

## 10. Checklist de seguridad por fase (resumen operativo)

| Control | Fase 1 (actual→estable) | Fase 2 (escala) |
|---|---|---|
| JWT access corto + refresh con rotación | ✅ obligatorio | mantiene |
| Rate limiting distribuido (Redis) | ✅ obligatorio | mantiene |
| Secretos fuera del compose | ✅ obligatorio | + gestor de secretos cloud |
| HTTPS/TLS | ✅ obligatorio en cualquier entorno público | + HSTS |
| RBAC por fila | ✅ obligatorio | + permisos granulares (`permisos`) |
| Logs de auditoría clínica | ✅ obligatorio | + alertas automáticas |
| WAF (Cloudflare o proveedor) | opcional | recomendado |
| Pentest externo | no aplica aún | recomendado antes de escalar a más instituciones |

Este documento es normativo: ningún endpoint o feature nuevo se implementa sin pasar por §1-§7 aplicables. Los hallazgos que aquí se corrigen están listados con severidad en `01_AuditoriaProyecto.md` §9 y priorizados en `13_Backlog.md`.

---

## 11. Estado de implementación (2026-07-29)

| Control | Estado en código | Ubicación |
|---|---|---|
| JWT 15 min + `jti` | ✅ | `app/security/crypto.py` |
| Refresh token opaco + rotación + reuse-detection | ✅ | `app/services/auth_service.py`, tabla `refresh_tokens` |
| Logout (revoca refresh + denylist JWT) | ✅ | `app/security/rbac.py`, `/api/v1/auth/logout` |
| RBAC permisos desde BD | ✅ | `app/security/rbac.py`, migración `0002` |
| Control por fila (`ninos`) | ✅ | `can_access_nino()` |
| bcrypt rounds ≥ 12 | ✅ | `app/security/crypto.py`, `NUTRIKIDS_BCRYPT_ROUNDS` |
| Política de contraseñas | ✅ | `validate_password_policy()` |
| Historial contraseñas (5 últimas) | ✅ | tabla `password_history`, migración `0008` |
| Reset password token 30 min | ✅ | `/api/v1/auth/password/forgot\|reset` |
| Rate limiting Redis + fallback memoria | ✅ | `app/security/rate_limit.py`, servicio `redis` en compose |
| Bloqueo tras 5 intentos login | ✅ | tabla `login_attempts`, migración `0008` |
| Audit log (sin PII) | ✅ | `app/security/audit.py`, tabla `security_audit_logs` |
| Headers HTTP + CSP | ✅ | `main.py` middleware |
| CORS restringido | ✅ | `NUTRIKIDS_CORS_ORIGINS` |
| Secretos fuera de compose | ✅ | `docker-compose.yml` usa `.env` |
| Postgres solo localhost | ✅ | `127.0.0.1:5432:5432` |
| Endpoints v1 protegidos | ✅ | `get_security_context` + `require_permission` |
| Backup script | ✅ | `scripts/backup_postgres.sh` |

**Pendiente (fases posteriores):** HTTPS/gateway (T1.4), gestor secretos cloud, WAF, alertas Prometheus/Sentry, verificación email obligatoria en UI, Argon2id lazy migration.
