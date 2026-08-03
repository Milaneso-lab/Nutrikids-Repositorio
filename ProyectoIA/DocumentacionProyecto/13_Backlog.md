# 13 — Backlog

> Cada tarea incluye: Objetivo, Dependencias, Prioridad (Alta/Media/Baja), Complejidad (S/M/L/XL), Agente recomendado, Tiempo estimado, Criterios de aceptación. Organizado por fase de `12_Roadmap.md`. Este backlog es vivo: se actualiza en `Bitacora.md` cada vez que se cierra o añade una tarea.

**Leyenda de agente recomendado**: `Claude Code` (cambios de codebase multi-archivo, refactor, backend), `Cursor` (edición asistida rápida in-IDE, cambios acotados), `Antigravity` (tareas de scaffolding/generación de UI o de proyecto nuevo, p. ej. init de la app RN), `ChatGPT` (research, redacción de contenido no técnico, revisión de copy). Ver `FlujoTrabajoIA.md` para el detalle de por qué cada uno.

---

## Épica RC — Release Candidate ✅ (2026-07-29)

### TRC.1 — Guard seeds producción ✅
- Seeds dev bloqueados en `production`/`staging` vía `NUTRIKIDS_ENVIRONMENT` y `NUTRIKIDS_ENABLE_DEV_SEED`.

### TRC.2 — Demo mode default false ✅
- Móvil: `EXPO_PUBLIC_DEMO_MODE=false` por defecto; opt-in explícito para demos.

### TRC.3 — Docker production-ready ✅
- FastAPI sin `--reload`; health Flask; FastAPI independiente de Laravel.

### TRC.4 — Health checks profundos ✅
- FastAPI `/health` valida PostgreSQL.

### TRC.5 — CI ampliado ✅
- Jobs: Laravel PHPUnit, FastAPI unit, móvil typecheck + Jest.

### TRC.6 — Estructura tests móvil ✅
- Jest + smoke test validadores comunicación.

### TRC.7 — Documentación RC ✅
- README, CHANGELOG, ROADMAP, guías `docs/`, `17_ReleaseCandidate.md`.

### TRC.8 — Script verificación ✅
- `scripts/verify-rc.ps1`

**Pendiente post-RC:** T4.3 PIN, gateway TLS, tests integración CI, limpieza navegación legacy.

---

## Fase 0 — Estabilización

### T0.2 — Extraer secretos hardcodeados del compose ✅ (2026-07-29)
- **Estado**: `docker-compose.yml` usa `${POSTGRES_PASSWORD}`, `${FLASK_SECRET_KEY}`, `env_file`. **Pendiente:** rotación secretos producción.

### T0.1 — Cerrar exposición de PostgreSQL al host ✅ (parcial — 2026-07-29)
- **Estado**: Puerto mapeado a `127.0.0.1:5432:5432` (solo localhost). **Pendiente:** eliminar mapeo en producción pura Docker network.

### T0.3 — Unificar `.env.example`
- **Objetivo**: un único `.env.example` por servicio, sin duplicados de variables, con el puerto real de Flask (5000) consistente en todos.
- **Dependencias**: ninguna.
- **Prioridad**: Media. **Complejidad**: S. **Agente**: Cursor. **Tiempo estimado**: 20 min.
- **Criterios de aceptación**: ningún archivo `.env.example` define la misma variable dos veces con valores distintos.

### T0.4 — Resolver `InfanteController.php` vacío
- **Objetivo**: decidir (según avance de ADR-006/Fase 2) si se elimina o se completa mínimamente; en Fase 0 se documenta la decisión, no se implementa aún si depende del nuevo esquema.
- **Dependencias**: ADR-006 (`14_DecisionesArquitectura.md`).
- **Prioridad**: Baja. **Complejidad**: S. **Agente**: Claude Code. **Tiempo estimado**: 15 min (decisión) / se reabre en Fase 2 para implementación real.
- **Criterios de aceptación**: no queda ningún archivo PHP vacío sin explicación en el repo.

### T0.5 — Eliminar carpeta `web1_flask/`
- **Objetivo**: confirmar que no está referenciada en `docker-compose.yml` ni en ningún script, y eliminarla.
- **Dependencias**: ninguna.
- **Prioridad**: Baja. **Complejidad**: S. **Agente**: Claude Code. **Tiempo estimado**: 10 min.
- **Criterios de aceptación**: build y `docker-compose up` funcionan igual tras la eliminación.

---

## Fase 1 — Fundación API + seguridad

### T1.1 — Añadir contenedor Redis al stack ✅ (2026-07-29)
- **Estado**: Servicio `redis` en `docker-compose.yml` con healthcheck y volumen persistente.

### T1.2 — Rate limiting distribuido en Redis ✅ (2026-07-29)
- **Estado**: `app/security/rate_limit.py` con backend Redis + fallback memoria. **Pendiente:** validar con 2 réplicas API.

### T1.3 — Refresh tokens con rotación ✅ (2026-07-29)
- **Estado**: Tabla `refresh_tokens` + endpoints `/api/v1/auth/refresh`, `/logout`, rotación y reuse-detection en `AuthService`. Access token 15 min. **Pendiente:** integración Laravel (T1.5).

### T1.6 — Normalizar `roles`/`permisos` ✅ (2026-07-29)
- **Estado**: RBAC operativo vía `require_permission()` y permisos en BD. Control por fila en `ninos`. **Pendiente:** `RoleMiddleware` Laravel consulte API/RBAC (T1.5).
- **Objetivo**: introducir el contenedor `gateway` con TLS local autofirmado para desarrollo, enrutando a `laravel`, `flask`, `api`.
- **Dependencias**: ninguna.
- **Prioridad**: Media. **Complejidad**: M. **Agente**: Claude Code. **Tiempo estimado**: 1 día.
- **Criterios de aceptación**: todo el tráfico externo pasa por el gateway; los puertos individuales (8080/8000/5000) dejan de ser necesarios para el usuario final (se conservan solo para debug interno).

### T1.5 — Laravel consume `/auth/login` de la API en vez de `password_verify` propio
- **Objetivo**: `AuthController.php` deja de leer `contrasena` directamente; llama a la API y guarda el JWT recibido en la sesión de Laravel.
- **Dependencias**: T1.3.
- **Prioridad**: Alta. **Complejidad**: M. **Agente**: Claude Code. **Tiempo estimado**: 1 día.
- **Criterios de aceptación**: el flujo de login visible para el usuario no cambia; `AuthController.php` ya no importa `password_verify` ni lee `contrasena` directamente de Eloquent.

### T1.6 — Normalizar `roles`/`permisos` ⚠️ (BD hecha — 2026-07-28)
- **Objetivo**: implementar el esquema de `03_BaseDatos.md` §3.2 con datos equivalentes a los 3 roles actuales (sin exponer aún permisos granulares en UI).
- **Dependencias**: ninguna.
- **Prioridad**: Media. **Complejidad**: M. **Agente**: Claude Code. **Tiempo estimado**: 1 día.
- **Criterios de aceptación**: `RoleMiddleware` sigue funcionando igual para el usuario final; internamente ya consulta `roles`/`permisos` en vez de un string libre.
- **Estado**: Tablas `roles`, `permisos`, `rol_permiso` + semilla en migración `20260728_0002`; `usuarios.rol_id` poblado desde `rol` legacy. **Pendiente:** adaptar `RoleMiddleware` para consultar RBAC (backend).

---

## Fase 2 — Migración del esquema de datos

### T2.1 — Alembic asume el esquema de negocio ✅ (implementación DB — 2026-07-28)
- **Objetivo**: inicializar Alembic en `fastapi/`, generar migración baseline desde el esquema actual, desactivar la propiedad de Laravel sobre las tablas de negocio.
- **Dependencias**: ADR-003.
- **Prioridad**: Alta. **Complejidad**: L. **Agente**: Claude Code. **Tiempo estimado**: 2-3 días.
- **Criterios de aceptación**: `alembic upgrade head` reproduce el esquema actual sin pérdida de datos en un entorno de prueba; Laravel deja de tener migraciones nuevas para tablas de negocio a partir de este punto.
- **Estado**: Alembic inicializado; 7 migraciones creadas en `fastapi/alembic/versions/` (0001 baseline → 0007 gamificación). `sql/schema_postgres.sql` actualizado. Revisión 2026-07-29: secuencia `ninos` y `rol_id NOT NULL` corregidos. **Pendiente:** ejecutar en staging/producción y congelar nuevas migraciones Laravel de negocio.

### T2.2 — Consolidación `pacientes`+`infantes` → `ninos` ⚠️ (parcial — 2026-07-28)
- **Objetivo**: migración de datos según ADR-006, incluyendo estrategia para poblar `padre_id` en registros existentes (requiere decisión de negocio: ¿se solicita a cada padre confirmar sus hijos existentes, o se infiere de algún dato disponible?).
- **Dependencias**: T2.1.
- **Prioridad**: Alta. **Complejidad**: XL. **Agente**: Claude Code (implementación) + decisión de negocio del equipo humano (no delegable a un agente).
- **Tiempo estimado**: 3-5 días + tiempo de decisión de negocio (no técnico).
- **Criterios de aceptación**: cero pérdida de datos de `pacientes`/`evaluaciones`/`menus`/`reportes` existentes; cada `nino` migrado sin `padre_id` conocido queda marcado explícitamente para resolución manual, no se inventa un valor.
- **Estado**: Tabla `ninos` creada; migración SQL en `20260728_0003` preserva IDs y marca `requiere_vinculacion_padre=true` con usuario sistema placeholder. Tablas `pacientes`/`infantes` **no eliminadas**. **Pendiente:** resolución humana de padres reales + actualización de modelos/API para usar `ninos`.

### T2.3 — Implementar `alertas`, `alergias`, `notas_nutriologo`, `menus_semanales` reales ⚠️ (BD hecha — 2026-07-28)
- **Objetivo**: reemplazar las tablas cáscara por el esquema de `03_BaseDatos.md` §4.2-4.4 y §4.6, con endpoints CRUD en la API.
- **Dependencias**: T2.1, T2.2.
- **Prioridad**: Alta. **Complejidad**: L. **Agente**: Claude Code. **Tiempo estimado**: 3-4 días.
- **Criterios de aceptación**: cada entidad tiene al menos un endpoint funcional cubierto por tests de integración (`10_Pruebas.md`).
- **Estado**: Columnas y FKs en migración `20260728_0005`. Endpoints v1 en `/api/v1/alergias`, `/alertas`, `/notas-nutriologo`, `/menus-semanales` (2026-07-29). **Pendiente:** tests de integración con PostgreSQL + RBAC (Fase 1).

### T2.5 — API REST v1 completa (Clean Architecture) ✅ (2026-07-29)
- **Objetivo**: implementar `/api/v1/*` según `04_API.md` con capas repository/service/DTO, paginación, errores uniformes, OpenAPI.
- **Dependencias**: T2.1.
- **Prioridad**: Alta. **Complejidad**: XL. **Agente**: Claude Code.
- **Criterios de aceptación**: 38 endpoints v1 documentados en Swagger; legacy `/api/*` intacto y deprecated; tests base passing.
- **Estado**: Implementado en `fastapi/app/`. **Pendiente:** auth/RBAC sobre v1 (Fase 1), tests integración con BD real, migración clientes Laravel/Flask.
- **Objetivo**: completar ADR-002 para pacientes, evaluaciones, menús, reportes, citas.
- **Dependencias**: T2.1-T2.3.
- **Prioridad**: Alta. **Complejidad**: XL. **Agente**: Claude Code. **Tiempo estimado**: 1-2 semanas (por volumen de controladores a migrar).
- **Criterios de aceptación**: ningún controlador de Laravel invoca `Model::create()/update()/delete()` sobre entidades de negocio clínico; todos pasan por HTTP a la API.

---

## Fase 3 — Gamificación (backend)

### T3.1 — Esquema y endpoints de hábitos/retos/logros/puntos/recompensas ⚠️ (BD hecha — 2026-07-28)
- **Objetivo**: implementar `03_BaseDatos.md` §6 completo + endpoints de `04_API.md`.
- **Dependencias**: T2.1.
- **Prioridad**: Alta. **Complejidad**: L. **Agente**: Claude Code. **Tiempo estimado**: 1 semana.
- **Criterios de aceptación**: un nutriólogo puede asignar un hábito a un niño y consultar su progreso vía `/docs` (Swagger), con tests de cálculo de puntos/rachas pasando (`10_Pruebas.md`).
- **Estado**: 10 tablas + semillas en `20260728_0007`. Endpoints v1: hábitos, retos, logros, puntos, recompensas (2026-07-29). **Pendiente:** tests de rachas/puntos con BD real; CRUD catálogos admin (T3.2).

### T3.2 — Gestión de catálogos desde el panel Laravel
- **Objetivo**: CRUD de `habitos_catalogo`, `retos_catalogo`, `logros_catalogo`, `recompensas_catalogo` en el panel admin/nutriólogo.
- **Dependencias**: T3.1.
- **Prioridad**: Media. **Complejidad**: M. **Agente**: Cursor. **Tiempo estimado**: 3-4 días.
- **Criterios de aceptación**: admin/nutriólogo puede crear/editar/desactivar ítems de catálogo sin acceso directo a base de datos.

---

## Fase 4 — App móvil MVP

### T4.1 — Scaffolding del proyecto React Native ✅
- **Objetivo**: inicializar el proyecto con la estructura de `07_AppMovil.md` §10, navegación base y theming.
- **Dependencias**: ninguna (puede arrancar en paralelo a Fase 2-3).
- **Prioridad**: Alta. **Complejidad**: M. **Agente**: Antigravity. **Tiempo estimado**: 2-3 días.
- **Estado**: **Completado 2026-07-29** — proyecto en `NutriKidsMovil/` (Expo SDK 57, TS estricto, navegación placeholder, Axios, Zustand, tema Nunito).
- **Criterios de aceptación**: la app compila en iOS y Android, navega entre pantallas placeholder según el mapa de `07_AppMovil.md` §3. *(TypeScript verificado; ejecución en simulador pendiente de entorno local.)*

### T4.2 — Cliente API tipado (generado desde `openapi.json`)
- **Objetivo**: generar tipos/cliente TS desde el contrato de la API (`04_API.md` §5).
- **Dependencias**: T3.1.
- **Prioridad**: Alta. **Complejidad**: S. **Agente**: Claude Code. **Tiempo estimado**: 1 día.
- **Criterios de aceptación**: el cliente generado se actualiza con un comando reproducible cuando cambia el contrato de la API.

### T4.2b — Sistema de acceso padres (JWT) ✅
- **Objetivo**: splash, onboarding, bienvenida, login/registro/logout/recuperación contra `/api/v1/auth/*`.
- **Dependencias**: T4.1, capa seguridad API (Fase 1).
- **Prioridad**: Alta. **Complejidad**: M.
- **Estado**: **Completado 2026-07-29** — ver `07_AppMovil.md` §12 y `NutriKidsMovil/src/features/auth/`.
- **Criterios de aceptación**: login/registro/logout/refresh/sesión persistente; tokens en secure store; validaciones cliente; mensajes de error amigables.

### T4.2c — Centro de Administración Familiar ✅
- **Objetivo**: dashboard padre, CRUD completo de hijos, perfil con placeholders para gamificación/nutrición.
- **Dependencias**: T4.2b, endpoints `/api/v1/ninos/*` y `/api/v1/ninos/{id}/puntos`.
- **Prioridad**: Alta. **Complejidad**: L.
- **Estado**: **Completado 2026-07-29** — ver `07_AppMovil.md` §13 y `NutriKidsMovil/src/features/familia/`.
- **Criterios de aceptación**: padre puede registrar/editar/consultar/eliminar hijos; datos desde API; estados carga/error; UI preparada para retos/logros/hábitos/alimentación.

### T4.3 — Flujo Login PIN + vinculación de dispositivo
- **Objetivo**: implementar selección de perfil, login con PIN, vinculación con `codigo_vinculacion`.
- **Dependencias**: T4.1, T4.2, endpoint `vincular-dispositivo` de `04_API.md`.
- **Prioridad**: Alta. **Complejidad**: M. **Agente**: Antigravity + Claude Code (lógica de auth/almacenamiento seguro). **Tiempo estimado**: 4-5 días.
- **Criterios de aceptación**: un niño puede vincularse con un código generado por su padre y entrar con PIN; el token se guarda en Keychain/Keystore, no en `AsyncStorage` plano.

### T4.4 — Pantallas Home + Hábitos del día + Avatar básico + Puntos
- **Objetivo**: implementar el corte mínimo viable de `12_Roadmap.md` Fase 4.
- **Dependencias**: T4.3.
- **Prioridad**: Alta. **Complejidad**: L. **Agente**: Antigravity (UI) + Claude Code (integración de estado/API). **Tiempo estimado**: 1-2 semanas.
- **Estado**: **Parcial 2026-07-29** — UI infantil (T4.4a) + Motor de Progresión (T4.4b) + Hábitos (T4.5); ciclo E2E con API real pendiente de verificación en dispositivo.
- **Criterios de aceptación**: ciclo completo login→marcar hábito→ver puntos actualizados funcional en dispositivo real o simulador, con al menos un test E2E cubriéndolo (`10_Pruebas.md` §2.4).

### T4.4a — Experiencia infantil UI (dashboard, perfil, avatar, navegación) ✅
- **Objetivo**: experiencia lúdica del niño, claramente distinta al panel padre; placeholders para gamificación futura.
- **Dependencias**: T4.2c (perfiles hijos), API `PUT /ninos/{id}` + `GET /ninos/{id}/puntos`.
- **Prioridad**: Alta. **Complejidad**: L.
- **Estado**: **Completado 2026-07-29** — ver `07_AppMovil.md` §14 y `NutriKidsMovil/src/features/nino/`.
- **Criterios de aceptación**: dashboard y perfil infantil funcionales; avatar guardable vía API; navegación con tabs; pantallas futuras con "Próximamente"; typecheck pasa.

### T4.4b — Motor de Progresión (gamificación desacoplada) ✅
- **Objetivo**: sistema reutilizable de XP, niveles, monedas, energía, rachas, logros, insignias, misiones, inventario y mascota.
- **Dependencias**: T4.4a, API `GET /ninos/{id}/puntos`, `GET /ninos/{id}/logros`.
- **Prioridad**: Alta. **Complejidad**: XL.
- **Estado**: **Completado 2026-07-29** — ver `08_Gamificacion.md` y `NutriKidsMovil/src/features/progresion/`.
- **Criterios de aceptación**: motor desacoplado de pantallas; dashboard infantil consume snapshot real; typecheck pasa; documentación completa; listo para consumo por hábitos/retos/tienda futuros.

### T4.5 — Sistema Inteligente de Hábitos Saludables ✅
- **Objetivo**: registrar y dar seguimiento a hábitos saludables con gamificación positiva integrada al Motor de Progresión.
- **Dependencias**: T4.4b, API `/habitos-catalogo`, `/ninos/{id}/habitos`, `/registrar`, `/puntos`.
- **Prioridad**: Alta. **Complejidad**: XL.
- **Estado**: **Completado 2026-07-29** — ver `09_HabitosSaludables.md` y `NutriKidsMovil/src/features/habitos/`.
- **Criterios de aceptación**: registro funcional; integración progresión sin duplicar XP; mascota positiva; calendario y estadísticas; sin castigos; typecheck pasa.

### T4.6 — Sistema Inteligente de Comunicación y Acompañamiento ✅
- **Objetivo**: plataforma de interacción positiva padre-niño-mascota; centro notificaciones; recordatorios; infra push.
- **Dependencias**: T4.4b, T4.5, expo-notifications.
- **Prioridad**: Alta. **Complejidad**: XL.
- **Estado**: **Completado 2026-07-29** — ver `10_Comunicacion.md` y `NutriKidsMovil/src/features/comunicacion/`.
- **Criterios de aceptación**: centro notificaciones funcional; padre envía mensajes; niño recibe vía mascota; recordatorios; push provider-agnostic; sin mensajes negativos; typecheck pasa.

---

## Fase 5 en adelante

Las tareas de Fase 5 (retos/logros/recompensas/push), Fase 6 (migración Next.js) y Fase 7 (escala cloud) se detallan en el mismo formato **al iniciar cada fase**, no se pre-escriben aquí en detalle para evitar planificar contra requisitos que pueden cambiar con lo aprendido en fases previas — principio de backlog vivo. El punto de partida de cada una está ya descrito en `12_Roadmap.md`.
