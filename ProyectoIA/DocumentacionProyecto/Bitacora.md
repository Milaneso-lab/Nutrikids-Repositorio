# Bitácora.md — Historial Cronológico de Decisiones y Sesiones

> Registro append-only. Cada entrada nueva se añade al final. No se reescribe el historial, solo se corrige con una entrada nueva que aclara la anterior si hiciera falta.

---

## 2026-07-28 — Sesión 1: Auditoría y generación de documentación técnica completa

**Rol asumido**: Arquitecto Principal / Software Architect del proyecto NutriKids (sin desarrollar código, solo diseño y documentación, por instrucción explícita del usuario).

**Trabajo realizado**:
1. Auditoría técnica completa del repositorio verificada contra el código fuente (no contra documentación previa del propio repo): `composer.json`, `package.json`, `docker-compose.yml`, Dockerfiles, `routes/web.php`, todos los Controllers/Models/Middleware de Laravel, las 22 migraciones una por una, `fastapi/models.py`, `fastapi/main.py`, `fastapi/security.py`, `fastapi/config.py`, `flask/requirements.txt`, `README_SECURITY.md`.
2. Hallazgos críticos identificados y documentados en `01_AuditoriaProyecto.md`:
   - Autenticación duplicada entre Laravel (`password_verify` manual) y FastAPI (JWT/bcrypt).
   - Tablas cáscara sin columnas reales: `infantes`, `alertas`, `alergias`, `notas_nutriologo`, `menus_semanales` (solo `id`+`timestamps`, confirmado tanto en migraciones Laravel como en modelos SQLAlchemy espejo).
   - `pacientes` sin FK a padre ni a nutriólogo; duplicidad conceptual con `Infante` (vacío, sin uso en ningún flujo).
   - `InfanteController.php` vacío (1 byte).
   - PostgreSQL expuesto al host en `docker-compose.yml` pese a documentarse como "protegido".
   - Secretos hardcodeados en `docker-compose.yml`.
   - Rate limiting en memoria, no apto para múltiples réplicas.
   - `.env.example` con `FLASK_PUBLIC_URL` duplicada y puerto inconsistente con el real (5000 vs 5001 documentado).
   - Carpeta `web1_flask/` residual sin uso activo.
3. Generación completa de la documentación técnica en `ProyectoIA/DocumentacionProyecto/` (20 documentos + este archivo + `EstadoProyecto.md` + `README_IA.md`), cubriendo: arquitectura objetivo, diseño de base de datos, contrato de API, estrategia de seguridad, diseño web, diseño completo de app móvil de gamificación, infraestructura Docker, estrategia cloud, testing, deployment, roadmap por fases, backlog accionable, ADRs, matriz de riesgos, plan de modernización incremental, prompts reutilizables y flujo de trabajo entre agentes de IA.

**Incidente de sesión**: la ejecución se interrumpió por un error de conexión con la API tras completar los documentos `00`, `01` y `02` (con la tarea `03_BaseDatos.md` marcada `in_progress` pero sin archivo escrito todavía). Al reanudar, se verificó el estado real en disco (`ls` de la carpeta + `TaskList`) antes de continuar, confirmando que 00-02 estaban completos y correctos, y se continuó exactamente desde `03_BaseDatos.md` sin repetir la auditoría ni regenerar los documentos ya existentes.

**Decisiones de arquitectura tomadas en esta sesión** (detalle completo en `14_DecisionesArquitectura.md`): ADR-001 a ADR-008, cubriendo motor de base de datos, dueño único de lógica de negocio (API), dueño del esquema de datos, no reescritura de Laravel, introducción de Redis, consolidación de `Paciente`/`Infante`, JWT con refresh, y no-leaderboard entre niños de familias distintas en la app móvil.

**Estado al cierre de esta sesión**: documentación completa (todas las tareas de `13_Backlog.md` aún no ejecutadas — son las de implementación futura). Ningún archivo de código fuente del proyecto fue modificado, por instrucción explícita del usuario. Próximo paso sugerido: iniciar Fase 0 de `12_Roadmap.md` (estabilización) cuando el equipo/agente responsable decida comenzar la implementación.

---

## 2026-07-28 — Sesión 2: Implementación del esquema de base de datos objetivo

**Rol asumido**: Software Engineer Senior — Backend / Base de Datos (solo capa de datos, sin backend ni frontend).

**Trabajo realizado**:
1. Análisis comparativo entre esquema actual (22 migraciones Laravel + `fastapi/models.py`) y diseño en `03_BaseDatos.md`. Reporte generado en la respuesta de sesión al usuario.
2. Inicialización de **Alembic** en `fastapi/` como dueño del esquema de negocio (ADR-003).
3. Creación de 7 migraciones en `fastapi/alembic/versions/`:
   - `20260728_0001` — baseline Laravel (no-op)
   - `20260728_0002` — `roles`, `permisos`, `rol_permiso`, extensión de `usuarios`, semilla RBAC
   - `20260728_0003` — `ninos`, `nino_credenciales`, migración de datos desde `pacientes`
   - `20260728_0004` — `refresh_tokens`
   - `20260728_0005` — dominio clínico completo + `menu_items`
   - `20260728_0006` — `citas.nino_id`, `comentarios`/`discusiones` NOT NULL
   - `20260728_0007` — gamificación completa + semillas de catálogo
4. Actualización de `sql/schema_postgres.sql` como referencia del esquema objetivo completo.
5. Añadido `alembic==1.14.0` a `fastapi/requirements.txt`.
6. Documentación en `fastapi/alembic/README.md`.

**Decisiones y desviaciones documentadas respecto a `03_BaseDatos.md`**:
- Se conserva `usuarios.id_usuario` como PK (no `id`) por compatibilidad con Laravel/FastAPI existentes.
- Se conserva columna `contrasena` (no renombrada a `contrasena_hash`) por compatibilidad.
- Se conserva columna legacy `rol` string junto a `rol_id` FK (transición gradual).
- Se conservan tablas `pacientes` e `infantes` sin DROP (plan `16_PlanModernizacion.md` §Paso 5).
- Se conservan columnas `paciente_id` en evaluaciones/menús/reportes junto a `nino_id`.
- Niños migrados sin padre conocido: `padre_id` apunta a usuario sistema `sistema-migracion@nutrikids.internal` con `requiere_vinculacion_padre = true`.
- Columna adicional `requiere_vinculacion_padre` no estaba en el diseño original pero cumple criterio T2.2 de marcado explícito.

**Qué NO se modificó** (por restricción explícita): controladores, API, modelos Eloquent/SQLAlchemy, Docker, autenticación, frontend, React Native.

**Estado al cierre**: migraciones escritas y cadena Alembic verificada sintácticamente. **Pendiente:** ejecutar `alembic upgrade head` en entorno con PostgreSQL y resolver vinculaciones de padre pendientes.

---

## 2026-07-29 — Sesión 3: Continuación implementación BD (revisión y cierre)

**Rol asumido**: Software Engineer Senior — Backend / Base de Datos (continuación de sesión 2 interrumpida por límite de tokens).

**Contexto**: La sesión anterior (2026-07-28) completó las 7 migraciones Alembic y actualizó documentación, pero la petición al usuario quedó sin reporte comparativo ni verificación final.

**Trabajo realizado en esta sesión**:
1. Verificación del estado en disco: 7 migraciones Alembic presentes, `sql/schema_postgres.sql` completo, `EstadoProyecto.md`/`Bitacora.md`/`13_Backlog.md` ya actualizados parcialmente.
2. Validación de cadena Alembic: `python -m alembic history` confirma cadena lineal 0001→0007 (head).
3. Corrección en `20260728_0003`: `setval` de secuencia `ninos_id_seq` tras INSERT con IDs explícitos desde `pacientes` (evita colisiones en futuros INSERT).
4. Corrección en `20260728_0002`: `usuarios.rol_id` pasa a `NOT NULL` tras poblar desde columna legacy `rol`.
5. Reporte comparativo entregado al usuario (tablas reutilizables/nuevas/obsoletas/riesgos).

**Qué NO se pudo ejecutar**: `alembic upgrade head` — Docker Desktop no estaba activo en el entorno de desarrollo; PostgreSQL no accesible en `:5432`.

**Estado al cierre**: implementación de capa de datos **completa en código**. Ejecución en BD pendiente de entorno. No se modificaron controladores, API, frontend, Docker ni autenticación.

---

## 2026-07-29 — Sesión 4: Implementación API REST v1 (Clean Architecture)

**Rol asumido**: Software Engineer Senior — Backend / API REST.

**Alcance**: Solo backend FastAPI. Sin JWT, refresh tokens, RBAC, rate limiting, Docker, frontend, React Native (por restricción explícita).

**Trabajo realizado**:
1. Nueva estructura `fastapi/app/` con capas: `core/` (excepciones, paginación, handlers), `domain/` (utilidades IMC/edad), `repositories/`, `services/`, `schemas/v1/`, `api/v1/endpoints/`.
2. **38 endpoints** en `/api/v1/*` según `04_API.md`: usuarios, niños, evaluaciones, alergias, alertas, notas, menús, menús semanales, reportes, citas, contactos, comentarios, discusiones, gamificación (hábitos, retos, logros, puntos, recompensas), dashboard/estadísticas.
3. Modelos SQLAlchemy extendidos (`models.py` + `app/infrastructure/persistence/entities.py`) para `ninos`, gamificación y columnas clínicas nuevas.
4. Respuestas paginadas `{ data, meta }`, errores uniformes `{ error: { code, message, details } }`, OpenAPI en `/docs`.
5. Rutas legacy `/api/*` conservadas, marcadas `deprecated` en Swagger.
6. Tests base: `tests/unit/test_domain_utils.py`, `tests/integration/test_health.py` (6 tests passing).
7. Dependencias añadidas: `pytest`, `httpx` en `requirements.txt`.

**Decisiones / desviaciones documentadas**:
- Endpoints `/auth/*` **no incluidos en v1** — pertenecen a Fase 1 (JWT/refresh); legacy `/api/auth/*` sigue disponible.
- **Sin autorización en v1** — filtros por rol se simulan vía query params (`padre_id`, `nutriologo_id`); RBAC se añadirá en Fase 1.
- `/api/v1/reportes/{id}/pdf` devuelve datos JSON para Laravel/dompdf (no genera PDF en API, según `04_API.md` §6).
- Notificaciones push y configuraciones de sistema **no implementadas** — no están en `04_API.md`; pendientes fases posteriores.
- Inconsistencia código vs doc: legacy usa `pacientes`; v1 usa `ninos` como entidad principal (ADR-006).

**Estado al cierre**: API compila y tests unitarios/integración básicos pasan. Pendiente: ejecutar contra PostgreSQL con migraciones Alembic aplicadas.

---

## 2026-07-29 — Sesión 5: Implementación capa de seguridad (Fase 1)

**Rol asumido**: Security Software Engineer / Backend.

**Trabajo realizado**:
1. JWT access 15 min con `jti`/`iat`; refresh tokens opacos con rotación y detección de reuso (OWASP).
2. Endpoints `/api/v1/auth/login|register|refresh|logout|password/forgot|password/reset`.
3. RBAC desde tablas `roles`/`permisos`/`rol_permiso`; `require_permission()` y control por fila en `ninos`.
4. Protección de endpoints v1 (auth obligatoria salvo contactos POST y lectura pública comentarios/discusiones).
5. Rate limiting Redis + fallback memoria; bloqueo cuenta tras 5 intentos fallidos.
6. Audit log `security_audit_logs` + `login_attempts` + `password_history` (migración Alembic `0008`).
7. Headers CSP/HSTS, CORS configurable, sanitización audit logs.
8. Docker: Redis, secretos en `.env`, Postgres `127.0.0.1:5432`.
9. `fastapi/.env.example` documentado; script `scripts/backup_postgres.sh`.
10. Tests: 10 passing (`test_security_crypto.py` + existentes).

**Dependencias nuevas:** `redis==5.2.1`

**Estado al cierre:** Fase 1 seguridad core completa en código. Pendiente: gateway TLS, Laravel auth proxy, verificación email UI.

---

## 2026-07-29 — Sesión 6: Scaffolding app móvil React Native (T4.1)

**Rol asumido**: Senior React Native Engineer.

**Trabajo realizado**:
1. Creación de `NutriKidsMovil/` con **Expo SDK 57** + **TypeScript estricto** (`blank-typescript`).
2. Arquitectura **Feature First** + capas `core/`, `shared/`, `navigation/`, `services/`, `state/`, `features/` según `07_AppMovil.md` §10.
3. **React Navigation v7**: stack auth (Splash → Perfil → PIN), tab bar principal (Inicio, Hábitos, Retos, Avatar, Logros), modal Modo Padre.
4. Cliente **Axios** centralizado con interceptores, normalización de errores (`AppError`) y hooks de auth preparados (sin lógica JWT).
5. **Zustand** para estado global (`appStore`, `uiStore`) — decisión documentada vs Redux Toolkit por simplicidad del dominio gamificado.
6. Tema infantil NutriKids: paleta verde/naranja, tipografía **Nunito**, tokens de spacing/radii/sombras.
7. Componentes UI reutilizables: `Button`, `AppText`, `Card`, `Screen`, `LoadingOverlay`, `ErrorMessage`, `PlaceholderScreen`.
8. Almacenamiento: `expo-secure-store` (tokens futuros), `AsyncStorage` (datos no sensibles/cola offline).
9. Variables de entorno vía `app.config.ts` + `EXPO_PUBLIC_*`; plugins preparados para notificaciones y cámara.
10. `npm run typecheck` pasa sin errores.

**Decisiones tomadas**:
- **Expo managed workflow** en lugar de RN CLI bare: acelera desarrollo, soporta EAS Build, `expo-secure-store` equivalente a Keychain/Keystore (`07_AppMovil.md` §10).
- **Zustand** sobre Redux Toolkit: menos boilerplate para estado de sesión/UI; stores por feature se añadirán en T4.3–T4.4.
- **Alias `@/`** con `babel-plugin-module-resolver` para imports limpios.
- Pantallas **placeholder genéricas** únicas (`PlaceholderScreen`) — no pantallas finales ni lógica de negocio (criterio de aceptación del usuario).

**No implementado (por diseño de esta fase)**: auth JWT, cliente OpenAPI generado (T4.2), pantallas finales, gamificación, tests E2E.

**Estado al cierre:** T4.1 completada en código. Pendiente verificación en dispositivo físico/simulador (`npm start`).

---

## 2026-07-29 — Sesión 7: Sistema de acceso padres (JWT) en app móvil

**Rol asumido**: Senior Mobile Software Engineer.

**Trabajo realizado**:
1. Flujo completo: Splash (bootstrap sesión) → Onboarding (1ª vez) → Bienvenida → Login/Registro/Forgot/Reset.
2. Servicios: `authApi` (raw client), `authService`, `tokenManager` (refresh con cola), `sessionStorage` (secure store + AsyncStorage).
3. Interceptor Axios: refresh automático en 401, reintento de requests, logout si refresh falla.
4. Hook `useAuth` + `useAuthBootstrap`; validaciones cliente alineadas con política API (8 chars, mayúsc/minúsc/dígito).
5. UX: gradientes, skeleton, indicadores de red (`@react-native-community/netinfo`), accesibilidad en formularios.
6. Logout desde header de tabs; navegación condicional autenticado/no autenticado en `RootNavigator`.
7. `npm run typecheck` pasa.

**Endpoints:** `/api/v1/auth/login|register|refresh|logout|password/forgot|password/reset`

**Dependencias nuevas:** `expo-device`, `expo-linear-gradient`, `@react-native-community/netinfo`

**Pendiente:** T4.3 login PIN niño; prueba E2E en dispositivo con API levantada.

---

## 2026-07-29 — Sesión 9: Experiencia móvil infantil (Épica 4 — UI)

**Rol asumido**: Senior React Native Engineer / UX Designer infantil / Game Designer.

**Trabajo realizado**:
1. Módulo `NutriKidsMovil/src/features/nino/` con tema lúdico (`kidTheme`), store de sesión niño, servicios, hooks y 10+ componentes reutilizables.
2. **Dashboard infantil** (`ChildHomeScreen`): saludo, avatar, nivel/XP placeholder, racha, próximo reto, progreso diario, CTA aventura.
3. **Perfil infantil** (`ChildProfileScreen`): avatar, edad, nivel, racha/progreso semanal placeholder, insignias/logros placeholder, compañero virtual.
4. **Avatar** (`AvatarEditorScreen` + `KidAvatarPicker`): selección emoji/compañero, guardado vía `PUT /api/v1/ninos/{id}` (`avatar_config`).
5. **Navegación** `ChildNavigator` + tabs: Inicio, Mi Perfil, Retos, Logros, Más (hub → Hábitos, Alimentación, Avatar, Tienda, Config).
6. Pantallas no implementadas: `KidComingSoonView` con diseño amigable.
7. Entrada temporal desde panel padre ("🎮 Modo niño") — hasta T4.3 login PIN.
8. Animaciones: `react-native-reanimated` (FadeIn, spring, bounce mascota).
9. `npm run typecheck` pasa.

**Decisiones UX**:
- Paleta vibrante separada del panel padre (gradientes, bordes redondeados, emoji como iconografía).
- Sin datos clínicos en vista niño (ADR-008).
- Compañero virtual en `avatar_config.companion`.
- 5 tabs + hub "Más" para no saturar barra inferior.

**Librerías**: `expo-linear-gradient`, `react-native-reanimated` (ya instaladas).

**Pendiente**: T4.3 PIN/vinculación; T4.4b hábitos/puntos reales; confeti; notificaciones push.

---

## 2026-07-29 — Sesión 8: Centro de Administración Familiar (Épica 3)

**Rol asumido**: Senior React Native Engineer / Full Stack Mobile Developer.

**Trabajo realizado**:
1. Módulo `NutriKidsMovil/src/features/familia/` (Feature First): tipos, validaciones, servicios API + demo, hooks, componentes reutilizables y pantallas.
2. **Pantallas:** `FamilyDashboardScreen` (saludo, resumen, accesos rápidos, tarjetas por hijo), `ChildFormScreen` (registrar/editar), `ChildProfileScreen` (detalle + placeholders retos/logros/hábitos/alimentación).
3. **Navegación:** `FamilyNavigator` como pantalla principal tras login padre (`RootNavigator` → `sessionPhase === 'parent'`). Tabs gamificación reservadas para sesión niño (T4.3+).
4. **API:** `GET/POST/PUT/DELETE /api/v1/ninos`, `GET /api/v1/ninos/{id}/puntos`. Se añadió `DELETE /ninos/{id}` (soft delete) en FastAPI — antes existía en servicio pero no estaba expuesto.
5. **Modo demo:** `demoNinosService` con AsyncStorage y datos semilla (Sofía, Mateo) cuando `EXPO_PUBLIC_DEMO_MODE=true`.
6. **UX:** estados carga/error/reintento, pull-to-refresh, confirmación antes de eliminar, selector avatar (emoji + foto local), validaciones completas de formulario.
7. `npm run typecheck` pasa.

**Endpoints consumidos:**
- `GET /ninos` (paginado `{ data, meta }`)
- `POST /ninos`
- `GET /ninos/{id}`
- `PUT /ninos/{id}`
- `DELETE /ninos/{id}`
- `GET /ninos/{id}/puntos`

**Campos extendidos vía `avatar_config` JSON:** `objetivoNutricional`, `nivelInicial`, `photoUri` (local hasta endpoint de subida).

**Dependencias nuevas:** `expo-image-picker`, `@react-native-community/datetimepicker`

**Riesgos detectados:**
- Fotos de avatar solo en URI local; falta endpoint de upload en API.
- Objetivo nutricional y nivel inicial no tienen columnas propias en BD — almacenados en `avatar_config`.
- IMC/estado nutricional clínico: placeholder hasta épica nutrición.

**Pendiente:** T4.3 login PIN niño; T4.4 gamificación; prueba E2E CRUD con API real (`EXPO_PUBLIC_DEMO_MODE=false`).

---

## 2026-07-29 — Sesión 10: Motor de Progresión (Épica 4 — T4.4b)

**Rol asumido**: Principal Game Software Engineer / Senior React Native Engineer / Game UX Designer.

**Trabajo realizado**:
1. Módulo `NutriKidsMovil/src/features/progresion/` — arquitectura Clean + Feature First con 21 archivos.
2. **Dominio:** tipos, config, calculadoras (XP, racha, energía, mascota), factory de snapshot, event bus.
3. **Infraestructura:** repositorio (AsyncStorage + sync API parcial), `ProgressionEngine` orquestador, fachadas de servicios, store Zustand.
4. **Sistemas:** XP/niveles, monedas (historial), energía diaria, rachas con bonus, logros, insignias (rareza), misiones (diaria/semanal/especial), inventario, mascota (evolución/humor/accesorios).
5. **UI:** `ProgressionHud`, `ProgressionDashboardSection`, `ProgressionCelebrationOverlay`; integración en `ChildHomeScreen` y `ChildProfileScreen`.
6. **Provider:** `ProgressionProvider` en `AppProviders` con bootstrap automático al entrar en modo niño.
7. Documentación: `08_Gamificacion.md` (nuevo), actualizados `EstadoProyecto.md`, `13_Backlog.md`, `07_AppMovil.md` §15.
8. `npm run typecheck` pasa.

**Decisiones técnicas**:
- Fórmula nivel alineada con API: `floor(xp/100)+1`.
- Monedas/energía/rachas locales hasta columnas/endpoints API.
- Event bus para analytics/confeti/push futuros sin acoplar pantallas.
- CTA "Comenzar aventura" simula progreso de misiones (demo) hasta épica hábitos.

**Pendiente**: conectar `POST /habitos/.../registrar`; endpoints monedas/energía; tests E2E; confeti Lottie.

---

## 2026-07-29 — Sesión 11: Sistema Inteligente de Hábitos Saludables (Épica 6 — T4.5)

**Rol asumido**: Principal Mobile Product Engineer / Senior React Native Engineer / Game Designer / Especialista en Hábitos Saludables Infantiles.

**Trabajo realizado**:
1. Módulo `NutriKidsMovil/src/features/habitos/` — 25+ archivos, Clean Architecture + Feature First.
2. **API:** cliente completo para catálogo, asignación, registro y sync puntos.
3. **Servicios:** `habitsService`, `habitProgressionBridge` (integración Motor de Progresión sin duplicar XP).
4. **Pantallas:** HabitsHome (tracker diario), HabitCalendar, HabitStatistics.
5. **Componentes:** HabitCard, DailyHabitTracker, ProgressCalendar, PetReactionCard, RewardAnimation, HealthyActionButton, WeeklyProgressCard, StatisticsCard.
6. **UX:** solo recompensas positivas, mensajes alentadores, metas por edad, sin castigos.
7. **Navegación:** rutas stack + deep links; ChildHome y ChildMore apuntan a hábitos reales.
8. **Engine:** `applyHabitSideEffects`, `addCoinsReward` en ProgressionEngine.
9. Documentación: `09_HabitosSaludables.md`; actualizados EstadoProyecto, Backlog, 07, 08.
10. `npm run typecheck` pasa.

**Pendiente**: endpoint historial registros API; config hábitos desde panel padre; E2E dispositivo real.

---

## 2026-07-29 — Sesión 12: Sistema Inteligente de Comunicación y Acompañamiento (Épica 7 — T4.6)

**Rol asumido**: Principal Mobile Product Engineer / Senior React Native Engineer / Arquitecto / UX Designer infantil.

**Trabajo realizado**:
1. Módulo `NutriKidsMovil/src/features/comunicacion/` — 30+ archivos, Clean Architecture.
2. **Centro de notificaciones** con 8 categorías, filtros, estados lectura.
3. **Comunicación padre→niño**: plantillas, mensajes personalizados, recompensas virtuales; entrega vía mascota.
4. **Recordatorios inteligentes**: hidratación, alimentación, actividad, sueño, misiones — mensajes positivos.
5. **Push infra**: `PushProvider` interface + `ExpoPushProvider`; registro token API stub.
6. **Eventos/campañas**: semillas semanal, familiar, escolar.
7. **Bridge**: `communicationEventBridge` escucha `progressionEventBus`.
8. **Pantallas**: NotificationCenter, ChildMessages, RemindersSettings, SendFamilyMessage.
9. **Provider**: `CommunicationProvider` en AppProviders.
10. Documentación: `10_Comunicacion.md`; actualizados EstadoProyecto, Backlog, 07, Bitacora.
11. `npm run typecheck` pasa.

**Pendiente**: endpoints API mensajes/notificaciones; push remoto worker; mensajes nutriólogo.

---

## 2026-07-29 — Sesión 13: Release Candidate (Épica RC)

**Rol asumido**: Principal Software Architect / Lead Full Stack / QA / DevOps / Performance.

**Trabajo realizado**:
1. Auditoría integral P0–P3 documentada en `17_ReleaseCandidate.md`.
2. **Seguridad:** guard seeds (`NUTRIKIDS_ENVIRONMENT`, `NUTRIKIDS_ENABLE_DEV_SEED`); demo mode móvil default `false`.
3. **Docker:** FastAPI sin `--reload`; health Flask; FastAPI independiente de Laravel en compose.
4. **API:** `/health` con ping PostgreSQL (503 si degradado).
5. **CI:** jobs FastAPI unit + móvil typecheck/Jest en `.github/workflows/tests.yml`.
6. **Tests:** pytest unit (8), Jest móvil (4); `scripts/verify-rc.ps1`.
7. **Limpieza:** DemoModeBanner duplicado; `.gitignore` ampliado.
8. **Documentación:** README, CHANGELOG, ROADMAP, 6 guías en `docs/`, EstadoProyecto, Backlog, 10_Pruebas.

**Decisiones técnicas**:
- RC no elimina navegación legacy móvil (riesgo regresión) — documentado en Roadmap v2.
- Tests integración FastAPI fuera de CI hasta job PostgreSQL dedicado.
- Modo demo explícito opt-in (`EXPO_PUBLIC_DEMO_MODE=true`) para demos académicas.

**Estado:** Proyecto en pausa post-RC. Próximo hito recomendado: T4.3 PIN niño.

---

## 2026-07-30 — Sesión: Modernización web (Fase 1 evolutiva)

**Rol asumido**: Principal Full Stack Engineer / UX — evolución sin reescritura.

**Trabajo realizado**:
1. Componentes Blade reutilizables (`stat-card`, `page-header`, `empty-state`).
2. **Admin Laravel:** rutas `nutriologos`, `estadisticas`, `auditoria`, `instituciones`; dashboard ampliado; `AuditLogService`.
3. **Nutriólogo:** dashboard con cumplimiento y recomendaciones; expediente con gráfica IMC y reportes.
4. **Portal padre Flask:** `/portal/*` (7 vistas) consumiendo API v1; sin gamificación web.
5. Documentación: `docs/web-modernizacion-fase1.md`.

**Decisiones:** Instituciones en JSON local (fase transitoria); auditoría agregada sin nueva tabla; identidad visual Flask/Laravel conservada.

**Pendiente:** FastApiClient en controladores Laravel; gráficas en portal padre; RBAC por fila nutriólogo.

---

## 2026-07-30 — Sesión: Portal Nutriólogo Fase 2 (clínico profesional)

**Trabajo realizado:**
1. Servicio `AntropometriaService` (IMC, series, cumplimiento).
2. Expediente por pestañas (10 secciones), búsqueda/filtros en pacientes.
3. Agenda/calendario, perfil profesional, módulo recomendaciones.
4. Planes alimenticios: estado, duplicar, historial.
5. Dashboard ampliado (consultas hoy, actividad reciente).
6. Reportes con gráfica mensual.
7. Migración `2026_07_30_180000_extend_portal_nutriologo_clinico.php`.
8. Documentación: `docs/web-portal-nutriologo-fase2.md`.

**Pendiente operativo:** ejecutar `php artisan migrate` en entorno Docker/local.

---

## 2026-07-30 — Sesión: Fase 6 Auth unificada + Portal Admin

**Trabajo realizado:**
1. `LoginService`, redirects por rol corregidos (padre → `/portal`).
2. RBAC Laravel: migración, `RolesPermisosSeeder`, middleware `permission`.
3. Admin: módulos roles, permisos, bitácora, catálogos.
4. Credenciales Fase 6 en seeders Laravel y FastAPI.
5. Documentación: `docs/fase6-auth-admin-portal.md`.

**Verificado:** login FastAPI + Laravel + Flask para admin, nutriólogo y padre.

---

## 2026-07-30 — Sesión: Profesionalización de la infraestructura de datos

**Trabajo realizado:**
1. Servicio `pgadmin` (`dpage/pgadmin4:8.14`) añadido a docker-compose en el puerto 5050,
   con volumen `pgadmin_data` y servidor precargado desde `docker/pgadmin/servers.json`.
2. Migración Alembic `20260730_0009_indices_integridad`: 19 índices sobre claves foráneas,
   7 índices compuestos de consulta y 10 restricciones CHECK clínicas.
3. Scripts de respaldo/restauración multiplataforma en `scripts/db/` y
   `sql/diagnostico_integridad.sql` para auditoría del esquema.
4. Configuración DBCode del workspace en `.vscode/settings.json`.
5. Credenciales embebidas retiradas de `settings.py`, `check_db.py`, plantillas `.env.*`,
   `pgloader` y documentación; `NUTRIKIDS_DATABASE_URL` ahora es obligatoria.
6. Datos simulados sacados del sembrado por defecto hacia `DemoContenidoSeeder`.
7. Documentación: `docs/infraestructura-datos-postgresql.md` y `docs/diccionario-datos.md`
   (este último generado desde el esquema vivo con `scripts/db/generar-diccionario.ps1`).

**Decidido:** PostgreSQL es la única fuente de datos; `ninos` es la entidad canónica y
`pacientes` queda como expediente heredado hasta su unificación.

**Pendiente:** unificar `ninos`/`pacientes`, retirar la tabla vacía `infantes` y
migrar `evaluaciones.peso`/`talla` (varchar) a las columnas numéricas.

---

## 2026-07-30 — Sesión: Cierre de credenciales embebidas (auditoría)

**Trabajo realizado:**
1. Claves de firma sin valor por defecto: `NUTRIKIDS_SECRET_KEY` y `FLASK_SECRET_KEY`
   ahora son obligatorias y de 32 caracteres mínimo; FastAPI y Flask abortan el arranque
   si faltan. Antes usaban los defaults públicos `change-this-key-in-production` y `change-me`.
2. `AdminTemporalSeeder` y `admin:crear-temporal` dejan de crear `admin@temp.com`/`admin123`:
   leen `ADMIN_TEMPORAL_EMAIL`/`ADMIN_TEMPORAL_PASSWORD`, se bloquean en producción y ya no
   imprimen la contraseña.
3. El recuadro de credenciales de demo del login de Flask queda tras
   `FLASK_SHOW_DEMO_CREDENTIALS` (por defecto `false`); antes publicaba usuarios y
   contraseñas reales en una página pública.
4. Placeholder `nutriologo123` retirado del campo de contraseña en `login.blade.php`.
5. CI y `fastapi/tests/conftest.py` provisionados con valores efímeros para que las
   validaciones estrictas no rompan la suite.

**Verificado:** los seis servicios siguen `healthy`; test negativo confirma que una clave
corta aborta el arranque; `pytest tests/unit/` pasa (8/8) simulando el entorno de CI.

**Pendiente:** la suite de PHPUnit no puede ejecutarse en el contenedor Laravel porque las
dependencias de desarrollo no están instaladas (`composer --no-dev`); se valida sólo en CI.

## Cómo añadir una entrada nueva

Formato: fecha (`AAAA-MM-DD`) — título breve de la sesión/hito, seguido de qué se hizo, qué se decidió, y qué queda pendiente. Cada agente que trabaje en el proyecto añade su propia entrada al terminar su sesión de trabajo, sin editar entradas anteriores.
