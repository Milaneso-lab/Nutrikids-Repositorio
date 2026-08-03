# EstadoProyecto.md — Estado Vivo del Proyecto

> **Este es el documento que debe leerse primero al retomar el proyecto**, incluso después de meses de inactividad. Se actualiza al final de cada sesión de trabajo relevante. Si este documento y el código real difieren, el código manda — pero se corrige este documento en el mismo cambio.

---

## Última actualización

**2026-07-29** — **Release Candidate 1.0.0-rc.1** (Épica RC): consolidación seguridad, Docker, CI, tests, documentación completa. Ver `17_ReleaseCandidate.md` y `Bitacora.md` sesión 13.

## Fase actual del roadmap

**Release Candidate — listo para entrega académica/profesional.**

- **BD:** PostgreSQL + Alembic 8 revisiones (0001–0008).
- **API v1:** 38+ endpoints JWT + RBAC; Redis para rate limit.
- **Infra:** Docker Compose con health checks (postgres, redis, fastapi, laravel, flask).
- **CI:** Laravel PHPUnit + FastAPI unit + móvil typecheck/Jest.
- **App móvil:** T4.1–T4.6 completos. Pendiente: **T4.3 login PIN niño** (entrada temporal "Modo niño").

**Pendiente Fase 1 restante:** Gateway TLS (T1.4), Laravel consume API auth (T1.5).

**Pendiente Fase 2:** Migración clientes Laravel/Flask a `/api/v1/*`.

## Qué existe hoy en el código (resumen)

- Tres aplicaciones web + app móvil conectadas a PostgreSQL: Laravel (:8080), FastAPI (:8000), Flask (:5000).
- **Redis** operativo en compose (rate limiting, denylist JWT).
- **App móvil:** Auth padres, Centro Familiar, Motor de Progresión, Hábitos, Comunicación.
- Seeds dev protegidos en production/staging.
- Modo demo móvil **desactivado por defecto** (opt-in con `EXPO_PUBLIC_DEMO_MODE=true`).

## Qué está completamente diseñado y listo para implementarse

Los documentos en `ProyectoIA/DocumentacionProyecto/` (00–17) cubren arquitectura, API, seguridad, app móvil, Docker, testing, deployment y RC. Guías operativas en `docs/`.

## Próximos pasos concretos (Roadmap v2 — post-RC)

1. T4.3 — login PIN + vinculación dispositivo.
2. T1.4 — Gateway TLS.
3. T1.5 — Laravel auth vía API.
4. CI integración FastAPI con PostgreSQL.
5. Migración Laravel/Flask a `/api/v1/*`.

Ver `ROADMAP.md` y `13_Backlog.md`.

## Decisiones ya tomadas que no deben reabrirse

Ver `14_DecisionesArquitectura.md`. Resumen: PostgreSQL único, FastAPI = negocio, Alembic esquema, Laravel backoffice, entidad `ninos`, sin leaderboard global.

## Riesgos activos

Ver `15_Riesgos.md`. Migración `pacientes`→`ninos` requiere decisión de negocio sobre `padre_id`.

## Cómo continuar

1. Leer `17_ReleaseCandidate.md`.
2. Ejecutar `.\scripts\verify-rc.ps1`.
3. Tomar tareas P0 de `ROADMAP.md`.
