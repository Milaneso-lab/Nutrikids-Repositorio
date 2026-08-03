# 17 — Informe Release Candidate (1.0.0-rc.1)

> Épica RC — Consolidación, estabilización y documentación. **Sin nuevas funcionalidades.**

**Fecha:** 2026-07-29  
**Versión objetivo:** 1.0.0-rc.1

---

## 1. Resumen ejecutivo

NutriKids alcanza estado **Release Candidate** apto para entrega académica/profesional. La plataforma web (Laravel + Flask + FastAPI + PostgreSQL + Redis) y la app móvil (auth, familia, progresión, hábitos, comunicación) están integradas, documentadas y verificables mediante scripts automatizados.

**Bloqueador conocido para producción real:** login PIN niño (T4.3) — entrada temporal vía "Modo niño" desde cuenta padre.

---

## 2. Auditoría — hallazgos y resolución

### P0 — Resueltos en RC

| # | Hallazgo | Acción |
|---|----------|--------|
| 1 | Demo mode ON por defecto en móvil | `EXPO_PUBLIC_DEMO_MODE=false` en `.env.example`; fallback `false` en `env.ts` y `app.config.ts` |
| 2 | Seeds dev sin guard en startup | `seed.py` verifica `NUTRIKIDS_ENVIRONMENT` y `NUTRIKIDS_ENABLE_DEV_SEED` |
| 3 | CI solo Laravel | Workflow ampliado: FastAPI unit + móvil typecheck/Jest |
| 4 | `/health` superficial | FastAPI valida `SELECT 1` en PostgreSQL; 503 si BD caída |
| 5 | FastAPI `--reload` en Docker | Eliminado del Dockerfile (producción) |

### P1 — Resueltos / mitigados

| # | Hallazgo | Acción |
|---|----------|--------|
| 6 | Flask sin healthcheck | `GET /health` + healthcheck en compose |
| 7 | FastAPI dependía de Laravel | Eliminado `depends_on: laravel` |
| 8 | DemoModeBanner duplicado | Removido de `FamilyDashboardScreen` (queda en `AppProviders`) |
| 9 | README raíz = boilerplate Laravel | Reescrito |
| 10 | EstadoProyecto contradictorio (Redis) | Corregido |
| 11 | `.gitignore` incompleto | Añadidos node_modules móvil, pycache, .expo |

### P2 — Documentados / Roadmap v2

| # | Hallazgo | Estado |
|---|----------|--------|
| 12 | T4.3 PIN niño | Pendiente — Roadmap v2 P0 |
| 13 | Navegación legacy móvil | Documentado; no eliminado (riesgo regresión) |
| 14 | Stubs features sin uso | Documentado en 07_AppMovil |
| 15 | `web1_flask/` residual | Pendiente confirmación eliminación |
| 16 | Tests integración FastAPI sin BD en CI | Roadmap v2 |
| 17 | Gateway TLS | Roadmap v2 P0 |

---

## 3. Optimizaciones realizadas

### Seguridad
- Guard de seeds en production/staging
- Demo mode opt-in explícito (`=true`)
- Health check expone degradación BD sin filtrar credenciales

### Docker
- Imagen FastAPI production-ready (sin reload)
- Health checks Flask + orden arranque optimizado
- FastAPI arranca independiente de Laravel

### Calidad
- 8 tests unitarios FastAPI (dominio + crypto)
- 4 tests unitarios móvil (validador mensajes)
- Script `scripts/verify-rc.ps1`
- CI tri-job (Laravel, FastAPI, móvil)

### Documentación
- 10 documentos nuevos/actualizados en raíz y `docs/`
- EstadoProyecto, Bitacora, Backlog alineados

---

## 4. Archivos creados

| Archivo |
|---------|
| `docs/GUIA_INSTALACION.md` |
| `docs/GUIA_DESPLIEGUE.md` |
| `docs/GUIA_DESARROLLADORES.md` |
| `docs/MANUAL_TECNICO.md` |
| `docs/MANUAL_USUARIO.md` |
| `docs/ARQUITECTURA_FINAL.md` |
| `ROADMAP.md` |
| `ProyectoIA/DocumentacionProyecto/17_ReleaseCandidate.md` |
| `scripts/verify-rc.ps1` |
| `fastapi/pytest.ini` |
| `NutriKidsMovil/jest.config.js` |
| `NutriKidsMovil/tsconfig.jest.json` |
| `NutriKidsMovil/src/features/comunicacion/domain/validators/__tests__/positiveMessageValidator.test.ts` |

## 5. Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `README.md` | Reescrito NutriKids |
| `CHANGELOG.md` | Entrada 1.0.0-rc.1 |
| `.gitignore` | Móvil, Python cache |
| `.github/workflows/tests.yml` | Jobs FastAPI + móvil |
| `docker-compose.yml` | Health Flask, deps FastAPI |
| `Dockerfile.fastapi` | Sin --reload |
| `fastapi/main.py` | Health con DB |
| `fastapi/seed.py` | Guard entorno |
| `fastapi/.env.example` | NUTRIKIDS_ENABLE_DEV_SEED |
| `flask/app.py` | GET /health |
| `NutriKidsMovil/.env.example` | DEMO_MODE=false |
| `NutriKidsMovil/app.config.ts` | Demo opt-in |
| `NutriKidsMovil/src/core/config/env.ts` | Fallback false |
| `NutriKidsMovil/package.json` | Scripts test |
| `NutriKidsMovil/tsconfig.json` | Exclude __tests__ |
| `NutriKidsMovil/.../FamilyDashboardScreen.tsx` | Banner duplicado |
| `ProyectoIA/.../EstadoProyecto.md` | Fase RC |
| `ProyectoIA/.../Bitacora.md` | Sesión 13 |
| `ProyectoIA/.../13_Backlog.md` | Items RC cerrados |
| `ProyectoIA/.../10_Pruebas.md` | Estructura tests RC |

---

## 6. Verificación ejecutada

| Check | Resultado |
|-------|-----------|
| FastAPI `pytest tests/unit/` | ✅ 8 passed |
| Móvil `npm run typecheck` | ✅ |
| Móvil `npm test -- --ci` | ✅ 4 passed |

---

## 7. Roadmap v2 (priorizado)

Ver `ROADMAP.md` completo. Top 5:

1. T4.3 — Login PIN niño
2. T1.4 — Gateway TLS
3. T1.5 + T2.4 — Laravel/Flask → API v1
4. CI integración FastAPI con PostgreSQL
5. T4.2 — Cliente OpenAPI tipado móvil

---

## 8. Criterios de aceptación RC

| Criterio | Estado |
|----------|--------|
| Documentación alineada con código | ✅ |
| Sin errores críticos conocidos bloqueantes demo | ✅ |
| Arquitectura consistente y documentada | ✅ |
| Código preparado para evolución | ✅ |
| Listo producción real sin intervención | ⏳ Requiere PIN + TLS + migración clientes |

---

## 9. Instrucciones post-RC

El proyecto queda en pausa operativa hasta nuevas instrucciones. Para continuar desarrollo, iniciar por items P0 del Roadmap v2.
