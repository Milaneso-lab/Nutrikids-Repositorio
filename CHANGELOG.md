# Changelog — NutriKids

Formato basado en [Keep a Changelog](https://keepachangelog.com/es/1.1.0/).

## [No publicado]

### Corregido

- **Alta de usuarios rompía con un 500 sin mensaje**: `usuarios.rol_id` es `NOT NULL` y ningún
  controlador lo rellenaba. Ahora `rol` y `rol_id` se derivan el uno del otro al guardar, tanto en
  Eloquent (`User::alinearRolYRolId`) como en SQLAlchemy (listeners sobre `Usuario`)
- Los formularios del panel devolvían JSON crudo al navegador: el nuevo trait `RespuestasCrud`
  responde JSON a las peticiones AJAX y redirección con mensaje flash a los formularios
- La configuración del sistema y el catálogo de instituciones ya no se descartan ni se guardan en
  un JSON en disco: tienen tablas `configuraciones` e `instituciones` en PostgreSQL
- Responder un mensaje de contacto guarda la respuesta en lugar de fingir el envío
- Las mediciones registradas en el panel rellenan `peso_kg`, `talla_cm` e `imc`, que son las
  columnas que leen la API y la app móvil
- La app móvil entiende el sobre de error de la API y muestra el mensaje real («Email ya
  registrado») en vez de «Ocurrió un error inesperado»

### Añadido

- Enlace `pacientes.nino_id`: el nutriólogo puede vincular un expediente con un niño registrado
  desde la app, y las mediciones y planes pasan a ser visibles en ambos clientes
- `DELETE /api/v1/usuarios/{id}` con guarda contra autoeliminación
- Manejadores globales de excepciones de base de datos en FastAPI con contrato JSON uniforme
- Retroalimentación en el panel: spinner y botón deshabilitado al enviar, y confirmación previa a
  las acciones destructivas
- `scripts/qa/verificar_persistencia.py`: 57 comprobaciones de CRUD real contra API, panel web y
  PostgreSQL, incluida la visibilidad cruzada web ↔ móvil
- `docs/estabilizacion-persistencia.md` con el informe completo de la fase

### Seguridad

- `NUTRIKIDS_SECRET_KEY` y `FLASK_SECRET_KEY` son obligatorias (mínimo 32 caracteres):
  se eliminan los valores por defecto `change-this-key-in-production` y `change-me`,
  que permitían firmar JWT y cookies de sesión con una clave pública
- `NUTRIKIDS_DATABASE_URL` obligatoria; PostgreSQL como única fuente de datos
- `AdminTemporalSeeder` y `admin:crear-temporal` toman las credenciales de
  `ADMIN_TEMPORAL_EMAIL`/`ADMIN_TEMPORAL_PASSWORD` y se bloquean en producción
- El recuadro de credenciales de demo del login de Flask requiere
  `FLASK_SHOW_DEMO_CREDENTIALS=true` (por defecto oculto)

### Infraestructura

- Servicio `pgadmin` en `docker-compose.yml` (puerto 5050) con servidor precargado
- Migración Alembic `0009`: índices sobre claves foráneas, índices de consulta y CHECK clínicos
- Scripts de respaldo y restauración en `scripts/db/` (bash y PowerShell)
- Conexión DBCode versionada en `.vscode/settings.json`

### Documentación

- `docs/infraestructura-datos-postgresql.md` y `docs/diccionario-datos.md`

## [1.0.0-rc.1] — 2026-07-29

### Release Candidate

Consolidación para entrega profesional. Sin nuevas funcionalidades de producto.

### Seguridad

- Seeds de desarrollo (`admin123`, etc.) protegidos por `NUTRIKIDS_ENVIRONMENT` y `NUTRIKIDS_ENABLE_DEV_SEED`
- Modo demo móvil desactivado por defecto (`EXPO_PUBLIC_DEMO_MODE=false`)
- Health check FastAPI valida conectividad a PostgreSQL

### Infraestructura

- `Dockerfile.fastapi` sin `--reload` (producción)
- Health check Flask en `docker-compose.yml`
- FastAPI ya no depende del arranque de Laravel en compose

### Calidad

- CI ampliado: Laravel PHPUnit + FastAPI unit + móvil typecheck/tests
- Estructura de tests unitarios móvil (Jest) con smoke test de validadores
- Script `scripts/verify-rc.ps1` para verificación local

### Documentación

- README raíz reescrito para NutriKids
- Guías en `docs/`: instalación, despliegue, desarrolladores, manual técnico, manual usuario, arquitectura
- `17_ReleaseCandidate.md` con informe técnico completo
- Actualizados `EstadoProyecto.md`, `Bitacora.md`, `13_Backlog.md`

### Conocido / pendiente post-RC

- T4.3 login PIN niño no implementado (entrada temporal "Modo niño")
- Laravel/Flask aún consumen BD directamente en parte del dominio
- Gateway TLS (T1.4) pendiente
- Tests de integración FastAPI requieren PostgreSQL en CI

---

## Histórico de épicas móvil (pre-RC)

### Épica 7 — Comunicación (T4.6)

Centro de notificaciones, mensajes padre→niño, recordatorios, infra push.

### Épica 6 — Hábitos saludables (T4.5)

Tracker diario, calendario, estadísticas, integración con motor de progresión.

### Épica 5 — Motor de progresión (T4.4b)

XP, niveles, monedas, energía, rachas, logros, misiones, mascota.

### Épica 4 — Centro familiar (T4.4a)

CRUD niños, dashboard padre, modo niño temporal.

### Épica 3 — Auth móvil (T4.1)

Login, registro, refresh, SecureStore.
