# 03 — Diseño de Base de Datos Objetivo

> Depende de: [`01_AuditoriaProyecto.md`](./01_AuditoriaProyecto.md) §4 (hechos verificados), [`02_Arquitectura.md`](./02_Arquitectura.md) §3.5 (FastAPI es dueño del esquema).
> **No contiene migraciones ni SQL ejecutable** — es el diseño conceptual/lógico que cualquier agente debe traducir a Alembic (FastAPI/SQLAlchemy) en la fase de implementación, con Laravel consumiendo el esquema solo vía API.

---

## 1. Motor de base de datos

**PostgreSQL** (se mantiene, ya en uso). Justificación breve (detalle en `14_DecisionesArquitectura.md`): soporta `JSONB` para atributos semi-estructurados (p. ej. configuración de retos/gamificación que cambia con frecuencia sin migración), tipos `ENUM` nativos, extensiones maduras (`pg_trgm` para búsqueda de texto en foro/contenido), particionado nativo para tablas de eventos de alto volumen (registro de hábitos diario de miles de niños), y es el estándar de facto para APIs FastAPI + SQLAlchemy + Alembic.

## 2. Convenciones de diseño

- Toda tabla usa clave primaria `id BIGINT` autoincremental. Se evita `id` compuesto salvo tablas de unión pura.
- Toda tabla de negocio tiene `created_at` / `updated_at` (timestamp con zona horaria, `timestamptz`). Las tablas de auditoría/eventos añaden `deleted_at` (soft delete) cuando el borrado debe ser reversible por ser dato clínico o legal.
- Nombres de tabla en `snake_case`, plural, en español (continuidad con el esquema actual: `usuarios`, `pacientes`, `citas`...).
- Toda FK declara `ON DELETE` explícito (nunca implícito): `CASCADE` para datos que no tienen sentido sin su padre (p. ej. detalle de evaluación), `RESTRICT`/`SET NULL` para relaciones donde el borrado del padre no debe destruir historial clínico.
- Los IDs nunca se exponen como único control de acceso: todo acceso pasa por RBAC en la API (ver `05_Seguridad.md`), no por "IDs difíciles de adivinar".

---

## 3. Dominio 1 — Identidad y cuentas

### 3.1 `usuarios`
Entidad raíz de identidad para adultos (padres, nutriólogos, admins). Reemplaza el `rol` como string libre actual por una relación a `roles` (normalización, sin perder la simplicidad operativa de hoy).

| Columna | Tipo | Restricciones | Nota |
|---|---|---|---|
| id | bigint PK | | |
| nombre, apellido_paterno | varchar(100) | not null | |
| apellido_materno | varchar(100) | null | |
| email | varchar(150) | **unique, not null** | índice único |
| contrasena_hash | varchar(255) | not null | bcrypt/argon2, nunca se expone |
| telefono | varchar(30) | null | |
| rol_id | bigint FK → roles.id | not null | ver 3.2 |
| estado | enum('activo','suspendido','pendiente_verificacion') | not null default 'pendiente_verificacion' | |
| email_verificado_en | timestamptz | null | |
| ultimo_login_en | timestamptz | null | para detección de anomalías (`05_Seguridad.md`) |
| created_at, updated_at | timestamptz | not null | |

**Índices**: único en `email`; índice en `rol_id`; índice en `estado` (consultas de admin filtran por estado con frecuencia).

### 3.2 `roles` y `permisos` (RBAC normalizado)
Sustituye el enum de 3 valores actual (`admin`, `nutriologo`, `padre`) por un esquema extensible, requerido porque el roadmap añade un cuarto actor (niño, con cuenta ligera — ver 3.4) y porque `05_Seguridad.md` exige permisos granulares (no solo rol).

- `roles`: id, nombre único (`admin`, `nutriologo`, `padre`, `nino`), descripcion.
- `permisos`: id, clave única (`pacientes.leer`, `pacientes.escribir`, `citas.asignar`, `contenido.moderar`, ...), descripcion.
- `rol_permiso` (tabla de unión N:M): rol_id FK, permiso_id FK, PK compuesta.

**Justificación de no sobre-diseñar desde el día 1**: se implementa el esquema completo desde ahora (bajo costo), pero operacionalmente en Fase 1 cada rol solo tendrá los permisos equivalentes al comportamiento actual — la granularidad fina se activa cuando el negocio la pida (p. ej. "nutriólogo junior" sin permiso de eliminar reportes), sin migración de esquema nueva.

### 3.3 `refresh_tokens`
No existe hoy (hallazgo de auditoría: JWT sin rotación). Necesaria para el flujo de `05_Seguridad.md`.

| Columna | Tipo | Nota |
|---|---|---|
| id | bigint PK | |
| usuario_id | bigint FK → usuarios.id, `ON DELETE CASCADE` | |
| token_hash | varchar(255) | se guarda el hash, nunca el token en claro |
| dispositivo | varchar(255) | null, para revocar por dispositivo (útil en móvil) |
| expira_en | timestamptz | not null |
| revocado_en | timestamptz | null |
| created_at | timestamptz | |

**Índice**: en `usuario_id` (listar/revocar sesiones activas de un usuario), en `expira_en` (job de limpieza).

### 3.4 `ninos` (reemplaza y consolida `pacientes` + `infantes`)

Resuelve el hallazgo #3 de la auditoría: hoy `pacientes` no tiene FK a un padre ni a un nutriólogo, e `Infante` es un duplicado vacío sin uso. El diseño objetivo unifica el concepto en **una sola entidad**, con vínculo explícito familiar y clínico, y añade lo necesario para la app móvil (avatar, cuenta ligera propia).

| Columna | Tipo | Restricciones | Nota |
|---|---|---|---|
| id | bigint PK | | |
| padre_id | bigint FK → usuarios.id | not null, `ON DELETE CASCADE` | el niño no existe sin su tutor responsable |
| nutriologo_asignado_id | bigint FK → usuarios.id | null, `ON DELETE SET NULL` | asignación clínica, puede no existir aún |
| nombre, apellidos | varchar(100) | not null | |
| fecha_nacimiento | date | not null | reemplaza `timestamp` impreciso actual |
| sexo | enum('masculino','femenino','otro') | not null | necesario para percentiles de crecimiento (OMS) |
| peso_actual_kg | numeric(5,2) | null | ver también historial en `evaluaciones` |
| talla_actual_cm | numeric(5,2) | null | idem — el valor "actual" es cache derivado de la última evaluación |
| avatar_config | jsonb | null | configuración del avatar de la app móvil (ver `07_AppMovil.md`) — JSONB porque cambia de forma libre sin requerir migración |
| codigo_vinculacion | varchar(12) | unique, null | código temporal para vincular el dispositivo del niño a la cuenta del padre (ver 3.5) |
| created_at, updated_at, deleted_at | timestamptz | soft delete: un expediente clínico infantil no se borra físicamente | |

**Índices**: `padre_id`, `nutriologo_asignado_id`, único en `codigo_vinculacion` (parcial: `WHERE codigo_vinculacion IS NOT NULL`).

**Restricción de negocio** (a nivel de aplicación, no de constraint SQL): `fecha_nacimiento` debe implicar edad entre 0 y 18 años en el momento del registro — validado en la API, no en la BD.

### 3.5 `nino_credenciales` (cuenta ligera del niño para la app móvil)

Separada de `usuarios` a propósito: un niño **no** es un `usuario` adulto con email/password completo (riesgo de PII innecesaria y de cumplimiento — ver `05_Seguridad.md` §COPPA/LOPD-menores). Es una credencial ligera vinculada 1:1 a `ninos`.

| Columna | Tipo | Nota |
|---|---|---|
| nino_id | bigint PK, FK → ninos.id | relación 1:1 |
| pin_hash | varchar(255) | PIN numérico corto (4-6 dígitos) o patrón, no contraseña adulta |
| dispositivo_id | varchar(255) | null, último dispositivo vinculado |
| vinculado_en | timestamptz | null |

---

## 4. Dominio 2 — Clínico (nutriólogo)

### 4.1 `evaluaciones`
Historial antropométrico. Se corrige el hallazgo de auditoría de `peso`/`talla` como `string` libre.

| Columna | Tipo | Nota |
|---|---|---|
| id | bigint PK | |
| nino_id | bigint FK → ninos.id, `ON DELETE CASCADE` | |
| nutriologo_id | bigint FK → usuarios.id, `ON DELETE SET NULL` | quién la realizó |
| peso_kg | numeric(5,2) | not null |
| talla_cm | numeric(5,2) | not null |
| imc | numeric(4,2) | calculado en la API al insertar, almacenado para consulta rápida en reportes históricos |
| percentil_oms | numeric(5,2) | null, calculado según tablas OMS por edad/sexo |
| recomendaciones | text | null |
| fecha_evaluacion | date | not null, default hoy |
| created_at, updated_at | timestamptz | |

**Índice**: `(nino_id, fecha_evaluacion DESC)` — patrón de acceso principal: "historial de un niño, más reciente primero".

### 4.2 `alergias` (reemplaza la tabla cáscara actual)

| Columna | Tipo | Nota |
|---|---|---|
| id | bigint PK | |
| nino_id | bigint FK → ninos.id, `ON DELETE CASCADE` | |
| tipo | enum('alimentaria','ambiental','medicamento','otra') | not null |
| descripcion | varchar(255) | not null (p. ej. "alergia al maní") |
| severidad | enum('leve','moderada','grave') | not null |
| registrada_por_id | bigint FK → usuarios.id | nutriólogo o admin que la registró |
| created_at, updated_at | timestamptz | |

**Índice**: `nino_id` (se consulta siempre en contexto de un niño, p. ej. al generar un menú, para excluir alérgenos automáticamente).

### 4.3 `alertas` (reemplaza la tabla cáscara actual)

Sistema de alertas clínicas y operativas (p. ej. "IMC fuera de rango", "cita vencida sin confirmar", "alergia grave sin revisar en el último menú").

| Columna | Tipo | Nota |
|---|---|---|
| id | bigint PK | |
| nino_id | bigint FK → ninos.id, null si es una alerta de sistema no ligada a un niño | `ON DELETE CASCADE` |
| tipo | varchar(50) | clave de tipo de alerta (catálogo controlado en la API, no en BD, para poder añadir tipos sin migración) |
| severidad | enum('info','advertencia','critica') | not null |
| mensaje | text | not null |
| atendida | boolean | not null default false |
| atendida_por_id | bigint FK → usuarios.id | null |
| atendida_en | timestamptz | null |
| created_at | timestamptz | |

**Índice**: `(atendida, severidad)` — patrón principal: "listar alertas críticas no atendidas".

### 4.4 `notas_nutriologo` (reemplaza la tabla cáscara actual)

| Columna | Tipo | Nota |
|---|---|---|
| id | bigint PK | |
| nino_id | bigint FK → ninos.id, `ON DELETE CASCADE` | |
| nutriologo_id | bigint FK → usuarios.id, `ON DELETE SET NULL` | |
| nota | text | not null |
| privada | boolean | not null default true — si es visible o no para el padre |
| created_at, updated_at | timestamptz | |

### 4.5 `menus` y `menu_items`

`menus` se mantiene como cabecera; se añade `menu_items` (hoy no existe: la tabla actual guarda una `descripcion` de texto libre, sin normalizar). Se corrige para permitir que la app móvil consuma comidas estructuradas (necesario para retos de "come tu menú del día").

- `menus`: id, nino_id FK, nutriologo_id FK, nombre, objetivo (varchar, p. ej. "control de peso"), fecha_inicio, fecha_fin, created_at, updated_at.
- `menu_items`: id, menu_id FK (`ON DELETE CASCADE`), dia_semana (enum lun-dom), tipo_comida (enum desayuno/colación/comida/cena), descripcion, calorias_aprox (int, null), created_at.

### 4.6 `menus_semanales` (reemplaza la tabla cáscara actual)
Plantilla reutilizable de menú (catálogo del nutriólogo/admin, no ligada a un niño específico) que luego se **instancia** como `menus` + `menu_items` para un niño concreto.

| Columna | Tipo | Nota |
|---|---|---|
| id | bigint PK | |
| nombre | varchar(150) | not null |
| descripcion | text | null |
| creado_por_id | bigint FK → usuarios.id | |
| publico | boolean | default false — si el admin lo publica como plantilla general |
| created_at, updated_at | timestamptz | |

### 4.7 `reportes`

| Columna | Tipo | Nota |
|---|---|---|
| id | bigint PK | |
| nino_id | bigint FK → ninos.id, `ON DELETE CASCADE` | |
| nutriologo_id | bigint FK → usuarios.id, `ON DELETE SET NULL` | |
| titulo | varchar(150) | not null |
| contenido | text | not null |
| pdf_generado_en | timestamptz | null (soporte al `laravel-dompdf` actual, generado en Laravel a partir de datos leídos de la API) |
| created_at, updated_at | timestamptz | |

### 4.8 `citas`
Se conserva el esquema actual (es la única tabla clínica ya bien diseñada según la auditoría), con una adición: `nino_id` opcional, porque hoy una cita se agenda solo a nivel de padre, sin especificar para cuál hijo.

| Columna añadida | Tipo | Nota |
|---|---|---|
| nino_id | bigint FK → ninos.id, null, `ON DELETE SET NULL` | permite historial de citas por niño cuando el padre tiene más de uno |

---

## 5. Dominio 3 — Comunidad / público

Se mantienen `contactos`, `comentarios`, `discusiones` con su esquema actual (verificado como correcto en la auditoría), sin cambios estructurales. Único ajuste: `comentarios.id_usuario` y `discusiones.id_usuario` pasan de nullable a **not null** — hoy permiten comentarios anónimos huérfanos, lo cual dificulta moderación y viola trazabilidad mínima; la API ya exige `auth` middleware para crear ambos, así que la BD debe reflejar esa garantía.

---

## 6. Dominio 4 — Gamificación (nuevo, para la app móvil)

Diseño necesario para `07_AppMovil.md`. Normalizado desde el inicio porque es el núcleo de valor del producto móvil.

### 6.1 `habitos_catalogo`
Catálogo maestro de hábitos saludables que se pueden asignar (p. ej. "beber agua", "comer verduras", "30 min de actividad física"), gestionado por admin/nutriólogo.

id, nombre, descripcion, categoria (enum: alimentacion/actividad/sueño/higiene), icono (varchar, referencia a asset), puntos_base (int), activo (boolean), created_at, updated_at.

### 6.2 `nino_habitos` (hábitos asignados a un niño)
id, nino_id FK, habito_id FK → habitos_catalogo, frecuencia (enum diaria/semanal), asignado_por_id FK → usuarios.id (nutriólogo), activo, created_at.

### 6.3 `habito_registros` (bitácora diaria — tabla de eventos, alto volumen)
id, nino_habito_id FK (`ON DELETE CASCADE`), fecha (date), completado (boolean), registrado_en (timestamptz).
**Restricción única**: `(nino_habito_id, fecha)` — un solo registro por hábito por día.
**Nota de escalabilidad**: candidata a particionado por rango de fecha (mensual) cuando el volumen crezca (miles de niños × hábitos × 365 días/año) — ver §8.

### 6.4 `retos_catalogo`
id, nombre, descripcion, tipo (enum: individual/semanal/especial), condicion (jsonb — regla flexible, p. ej. `{"habito":"beber_agua","dias_consecutivos":7}`, evita migración por cada reto nuevo), puntos_recompensa, activo, fecha_inicio, fecha_fin (null si es permanente).

### 6.5 `nino_retos` (progreso de un niño en un reto)
id, nino_id FK, reto_id FK → retos_catalogo, progreso (jsonb, estructura libre según `condicion` del reto), completado (boolean), completado_en (timestamptz, null), created_at.

### 6.6 `logros_catalogo` y `nino_logros`
Insignias permanentes (distinto de retos, que son temporales/repetibles). `logros_catalogo`: id, nombre, descripcion, icono, criterio (jsonb). `nino_logros`: id, nino_id FK, logro_id FK, obtenido_en (timestamptz). Único `(nino_id, logro_id)`.

### 6.7 `nino_puntos` (saldo agregado — tabla de resumen, no de eventos)
nino_id PK/FK, puntos_totales (int), nivel_actual (int), actualizado_en (timestamptz). Se recalcula/incrementa desde `habito_registros` y `nino_retos` vía lógica de la API (no triggers SQL, para mantener la lógica de negocio en un solo lugar — principio de §1 de `02_Arquitectura.md`).

### 6.8 `recompensas_catalogo` y `nino_recompensas`
Canje de puntos por recompensas (definidas por admin/nutriólogo, p. ej. "elige el menú del sábado"). `recompensas_catalogo`: id, nombre, descripcion, costo_puntos, stock (null = ilimitado). `nino_recompensas`: id, nino_id FK, recompensa_id FK, canjeado_en, estado (enum: pendiente/entregada).

---

## 7. Diagrama entidad-relación (simplificado, dominios principales)

```
roles ─┬─< usuarios >─┬─< ninos >─┬─< evaluaciones
       │              │           ├─< alergias
       permisos       │           ├─< alertas
       (N:M rol_permiso)│         ├─< notas_nutriologo
                       │           ├─< menus >─< menu_items
                       │           ├─< reportes
                       │           ├─< citas
                       │           ├─< nino_credenciales (1:1)
                       │           ├─< nino_habitos >─< habito_registros
                       │           ├─< nino_retos
                       │           ├─< nino_logros
                       │           ├─< nino_puntos (1:1)
                       │           └─< nino_recompensas
                       ├─< refresh_tokens
                       ├─< comentarios
                       └─< discusiones

habitos_catalogo ─< nino_habitos          retos_catalogo ─< nino_retos
logros_catalogo ─< nino_logros            recompensas_catalogo ─< nino_recompensas
menus_semanales (catálogo independiente, se instancia hacia `menus`)
```

---

## 8. Normalización, índices y escalabilidad

- **Nivel de normalización**: 3FN en el dominio transaccional (identidad, clínico, comunidad). Se usa `JSONB` deliberadamente (no normalización estricta) solo donde la estructura es genuinamente variable y de bajo volumen de escritura por fila (`avatar_config`, `condicion`/`progreso` de retos) — es una desnormalización consciente, no descuido, documentada aquí para que ningún agente la "corrija" sin entender el motivo.
- **Índices obligatorios desde el día 1**: todas las FK (Postgres no las indexa automáticamente salvo la PK del lado "uno"), más los compuestos señalados en cada tabla según el patrón de consulta dominante.
- **Tablas de alto volumen a vigilar**: `habito_registros` (crece con niños × hábitos × días) y `alertas` (crece con eventos del sistema). Se diseñan desde ahora con índices y candidatas a particionado por fecha para no requerir un rediseño reactivo.
- **Réplica de lectura**: se activa cuando reportes/analítica (fase de madurez, `09_Cloud.md`) empiecen a competir por recursos con las escrituras transaccionales — no es necesaria en Fase 1.
- **Soft delete** (`deleted_at`) se reserva para entidades con obligación de trazabilidad clínica o legal (`ninos`, y por extensión sus datos clínicos vía `ON DELETE CASCADE` intencional dentro del mismo niño). El resto usa borrado físico o `activo=false` según corresponda al dominio.

## 9. Futuras ampliaciones previstas (no se implementan ahora, pero el diseño no las bloquea)

- **Mensajería/chat padre-nutriólogo**: tabla `mensajes` (remitente_id, destinatario_id, nino_id, contenido, leido_en) — encaja sin fricción sobre el esquema de `usuarios`/`ninos` ya definido.
- **Multi-tenant (varias clínicas/instituciones usando NutriKids)**: requeriría introducir `organizaciones` como entidad raíz por encima de `usuarios`. Se documenta como decisión pendiente en `14_DecisionesArquitectura.md` — el esquema actual es single-tenant a propósito, no se sobre-diseña para un escenario no confirmado.
- **Internacionalización de contenido** (`habitos_catalogo`, `logros_catalogo`, etc.): se resolvería con tablas `_traducciones` satélite cuando haya un segundo idioma real, no antes.
