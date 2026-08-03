# 14 — Registro de Decisiones de Arquitectura (ADRs)

> Formato: cada decisión documenta contexto, decisión, alternativas consideradas y consecuencias. Este documento es **append-only**: una decisión superada no se borra, se marca como `Reemplazada por ADR-XXX` y se añade una nueva entrada.

---

## ADR-001 — Mantener PostgreSQL como motor de base de datos

**Contexto**: el proyecto ya usa PostgreSQL (verificado en `01_AuditoriaProyecto.md`) tanto desde Laravel como desde FastAPI.
**Decisión**: se mantiene PostgreSQL para todo el ecosistema, incluida la capa de gamificación nueva.
**Alternativas consideradas**: MySQL (descartado — sin ventaja real sobre Postgres para este dominio, y forzaría reescribir el esquema ya funcional), MongoDB (descartado — el dominio es fuertemente relacional: usuarios, niños, evaluaciones, citas, con integridad referencial crítica en datos clínicos; NoSQL añadiría complejidad de consistencia sin beneficio claro).
**Consecuencias**: se aprovecha `JSONB` para los pocos campos genuinamente semi-estructurados (`avatar_config`, `condicion` de retos — ver `03_BaseDatos.md` §8) en vez de forzar un motor documental para todo el sistema.

---

## ADR-002 — FastAPI como única fuente de lógica de negocio; Laravel y web pública se convierten en clientes de la API

**Contexto**: la auditoría (`01_AuditoriaProyecto.md` §5) detectó lógica de autenticación duplicada entre Laravel (`password_verify` manual) y FastAPI (`bcrypt.checkpw` + JWT), y Flask con acceso directo opcional a Postgres. Esto crea dos superficies donde un bug de seguridad puede introducirse de forma independiente.
**Decisión**: FastAPI concentra toda validación de negocio y acceso a datos. Laravel y la web pública dejan de escribir directamente en la base de datos para entidades de negocio y pasan a consumir `/api/v1/*`.
**Alternativas consideradas**: (a) mantener el status quo y solo sincronizar manualmente la lógica duplicada — descartado, no elimina el riesgo, solo lo documenta; (b) migrar todo a un único framework (todo Python o todo PHP) — descartado por costo de reescritura injustificado frente al valor obtenido, ver ADR-004.
**Consecuencias**: Laravel pierde autonomía de escritura directa sobre pacientes/evaluaciones/menús/reportes/citas, gana consistencia y una sola fuente de verdad. Introduce latencia de red interna adicional, mitigada por estar en la misma red privada (`02_Arquitectura.md` §7).

---

## ADR-003 — FastAPI (Alembic) pasa a ser dueño del esquema de base de datos; Laravel deja de emitir migraciones de negocio

**Contexto**: hoy Laravel es dueño del DDL (22 migraciones verificadas) y FastAPI solo refleja el esquema en modelos SQLAlchemy con `NUTRIKIDS_SKIP_CREATE_ALL=1` en Docker. Es una inversión respecto a ADR-002: si FastAPI es dueño de la lógica, debe ser dueño también del esquema que esa lógica valida.
**Decisión**: las migraciones de negocio (identidad, clínico, gamificación) se gestionan con Alembic desde FastAPI. Laravel conserva sus propias migraciones solo para tablas que le son propias como framework (sesiones, cache, colas — ya existentes y sin relación con el dominio de negocio).
**Alternativas consideradas**: mantener a Laravel como dueño del esquema y que FastAPI solo lo consuma — descartado porque perpetúa la inconsistencia de que el "cerebro" de negocio (FastAPI) no controle la forma de sus propios datos.
**Consecuencias**: requiere una migración de tooling cuidadosa (detallada en `16_PlanModernizacion.md`) para no romper el entorno de desarrollo actual durante la transición.

---

## ADR-004 — No reescribir Laravel; se mantiene como backoffice, se evalúa Next.js solo para la web pública

**Contexto**: el panel admin/nutriólogo ya está construido, probado y en uso. Reescribirlo en un stack distinto (p. ej. React/Node) tendría costo alto sin beneficio funcional inmediato, dado su bajo volumen de usuarios internos.
**Decisión**: Laravel se mantiene como backoffice, convertido en cliente de la API (ADR-002). La web pública (hoy Flask) es la que se evalúa migrar a Next.js en una fase 2, por las razones de SEO/tipos compartidos con React Native detalladas en `06_Web.md` §3.2.
**Alternativas consideradas**: unificar todo el frontend en un solo framework — descartado por ahora; se revisita si en el futuro el admin necesita interactividad de tipo SPA que Blade no ofrezca cómodamente.
**Consecuencias**: el equipo mantiene tres lenguajes de cliente (PHP/Blade, Python/Jinja→eventualmente TS/React, TS/React Native) — se acepta como costo razonable frente al de una reescritura no justificada por requisitos actuales.

---

## ADR-005 — Redis para cache, rate limiting distribuido y denylist de JWT

**Contexto**: el rate limiter actual es en memoria (`fastapi/deps.py`), lo que falla al escalar a más de una réplica de la API (hallazgo de auditoría #4).
**Decisión**: se introduce Redis desde la Fase 1 del roadmap (no se difiere), dado su bajo costo operativo y alto valor: resuelve rate limiting distribuido, cache de catálogos (hábitos, retos, logros) y denylist de JWT para logout inmediato.
**Alternativas consideradas**: mover el rate limiting a nivel de gateway únicamente (Nginx) — insuficiente por sí solo porque no cubre reglas de negocio finas (p. ej. límite por usuario autenticado, no solo por IP).
**Consecuencias**: un nuevo componente de infraestructura a operar y respaldar (aunque, al ser mayormente cache/efímero, no requiere backup con la misma criticidad que Postgres).

---

## ADR-006 — Consolidar `Paciente` e `Infante` en una única entidad `ninos`, con FK explícita a padre y nutriólogo

**Contexto**: la auditoría (`01_AuditoriaProyecto.md` §4.2-4.3) encontró que `Paciente` no tiene FK a un padre ni a un nutriólogo, e `Infante` es una tabla vacía sin uso en ningún flujo activo — duplicidad conceptual sin resolver.
**Decisión**: se diseña una única entidad `ninos` (`03_BaseDatos.md` §3.4) con `padre_id` y `nutriologo_asignado_id` explícitos, y una entidad satélite `nino_credenciales` para la cuenta ligera del niño en la app móvil.
**Alternativas consideradas**: mantener ambas entidades con roles distintos (p. ej. `Infante` = perfil de niño para app, `Paciente` = expediente clínico) — descartado por añadir complejidad de sincronización entre dos tablas que en la práctica describen al mismo sujeto; una tabla con las relaciones correctas es más simple y más segura (evita el riesgo de que un niño exista en una tabla y no en la otra).
**Consecuencias**: requiere una migración de datos de `pacientes` existentes hacia `ninos` con asignación retroactiva de `padre_id` (dato que hoy no existe en `pacientes` y deberá reconstruirse o solicitarse) — riesgo identificado y gestionado en `16_PlanModernizacion.md` y `15_Riesgos.md`.

---

## ADR-007 — JWT de acceso de vida corta (15 min) + refresh token con rotación, en vez del JWT de 120 min actual sin refresh

**Contexto**: `fastapi/config.py` define `access_token_minutes = 120` sin ningún mecanismo de refresh o revocación (hallazgo de auditoría).
**Decisión**: se reduce el access token a 15 minutos y se introduce refresh token opaco con rotación y detección de reuso (`05_Seguridad.md` §1.2, `03_BaseDatos.md` §3.3).
**Alternativas consideradas**: mantener el token largo por simplicidad — descartado por ampliar innecesariamente la ventana de daño ante un token filtrado, especialmente sensible tratándose de datos de menores.
**Consecuencias**: cada cliente (Laravel, web, móvil) debe implementar lógica de refresh automático — cargo de trabajo adicional pero estándar y bien documentado (`04_API.md` §1.5.9... referenciar endpoint `/auth/refresh`).

---

## ADR-008 — La app móvil no implementa leaderboard entre niños de familias distintas

**Contexto**: patrón común en apps de gamificación adulta, pero de riesgo psicológico en un producto dirigido a niños con temática de salud/peso.
**Decisión**: sin comparación social entre niños de distintas familias; la competencia es contra el progreso propio (rachas, niveles) — ver `07_AppMovil.md` §8.
**Alternativas consideradas**: leaderboard opcional/privado solo entre hermanos de la misma familia — no descartado a futuro, pero no forma parte del alcance inicial; requeriría diseño de producto adicional antes de implementarse.
**Consecuencias**: menor "gancho" de competencia viral entre usuarios, aceptado conscientemente a cambio de reducir riesgo de daño psicológico/comparación negativa en menores.

---

## Cómo añadir una nueva ADR

Cualquier agente que tome una decisión de arquitectura no trivial (elección de tecnología, cambio de esquema de datos, cambio de contrato de API) debe añadir una entrada aquí **en el mismo cambio**, siguiendo el formato Contexto/Decisión/Alternativas/Consecuencias. No se documenta en el mensaje de commit únicamente — el commit se pierde en el historial, este documento es la fuente de verdad consultable.
