# 10 — Estrategia de Pruebas

> Depende de: [`04_API.md`](./04_API.md), [`05_Seguridad.md`](./05_Seguridad.md), [`07_AppMovil.md`](./07_AppMovil.md).

---

## 1. Principio

La API (FastAPI) concentra la lógica de negocio (`02_Arquitectura.md` §1), por lo tanto concentra también el mayor esfuerzo de pruebas automatizadas. Los clientes (Laravel, web pública, app móvil) se prueban principalmente en la capa de integración/UI, no reimplementando pruebas de reglas de negocio que ya viven en la API.

## 2. Pirámide de pruebas por capa

### 2.1 API (FastAPI) — base de la pirámide
- **Unitarias**: reglas de negocio puras (cálculo de IMC/percentil, cálculo de racha, condición de reto cumplido, rotación de refresh token) con `pytest`, sin base de datos real (mocks/fixtures).
- **Integración**: cada endpoint contra una base de datos de prueba real (Postgres efímero, vía contenedor de test o `pytest-postgresql`), cubriendo happy path + casos de RBAC denegado (403/404 por fila, `05_Seguridad.md` §2) + validación (422).
- **Contrato**: verificación de que el `openapi.json` generado no rompe compatibilidad hacia atrás en cada PR (evita romper clientes móvil/web sin darse cuenta) — se ejecuta en CI (`11_Deployment.md`).
- **Seguridad**: pruebas específicas de rate limiting (excede el límite → 429), de expiración/rotación de JWT, de intento de acceso cross-tenant (padre A intentando leer `nino` de padre B → 404).

### 2.2 Laravel (Admin/Nutriólogo)
- **Feature tests** de Laravel (`phpunit.xml` ya configurado en el proyecto) sobre las rutas de backoffice, mockeando la respuesta de la API (no se re-testea la lógica de negocio, ya cubierta en 2.1) — se valida que Laravel transforma correctamente la respuesta de la API y aplica el `RoleMiddleware` como corresponde.
- **RBAC de UI**: verificar que un nutriólogo no ve enlaces/acciones de admin y viceversa (ya parcialmente cubierto por middleware, se formaliza en tests).

### 2.3 Web Pública (Flask / Next.js)
- Pruebas de integración de las llamadas a la API (mockeadas) para las páginas críticas (login, registro, contacto, calculadora).
- E2E ligero (Playwright/Cypress) sobre los flujos públicos principales: ver contenido, enviar contacto, iniciar sesión y llegar al home correspondiente al rol.

### 2.4 App Móvil (React Native)
- Unitarias de lógica de cliente (cálculo de racha visual, formateo, manejo de estado de retos) con Jest.
- Component tests de pantallas críticas (checklist de hábitos, canje de recompensas) con React Native Testing Library.
- E2E con Detox (o equivalente) sobre el flujo crítico: login con PIN → marcar hábito → ver puntos actualizados — al menos para Android/iOS antes de cada release a tienda.

## 3. Datos de prueba

- Semillas (`seed.py` ya existe en `fastapi/`, se mantiene y se amplía) para poblar entornos de desarrollo/staging con datos ficticios realistas (niños, hábitos, retos) — nunca datos reales de menores en entornos no productivos.
- Fixtures de RBAC: al menos un usuario de cada rol (`admin`, `nutriologo`, `padre`) y un niño vinculado, versionados en el repo de tests para reproducibilidad.

## 4. Cobertura mínima exigida (gate de CI)

| Capa | Cobertura mínima recomendada |
|---|---|
| API — lógica de negocio y auth | 80% |
| API — endpoints (integración) | 100% de endpoints con al menos un test happy-path + un test de RBAC denegado |
| Laravel/Flask — feature tests | Rutas críticas (auth, formularios públicos, acciones admin destructivas) |
| App móvil | Flujo crítico E2E cubierto antes de cada release |

No se exige 100% de cobertura de línea en ningún punto — se prioriza cobertura de **reglas de negocio y de seguridad**, que es donde un bug es más costoso (dato clínico de un menor expuesto o mal atribuido).

## 5. Pruebas de carga (fase de madurez, no MVP)

Antes de escalar a producción con tráfico real (Etapa 2 de `09_Cloud.md`), se ejecuta una prueba de carga básica (k6 o Locust) sobre los endpoints de mayor tráfico esperado (login, registro de hábito diario) para validar que el rate limiting distribuido (`05_Seguridad.md` §4) y el balanceo (`09_Cloud.md` §2) se comportan como se diseñaron.

## 6. Dónde vive esto en CI/CD

El detalle de en qué momento del pipeline corre cada tipo de prueba está en `11_Deployment.md` §2 — este documento define **qué** se prueba, no **cuándo** dentro del pipeline.

---

## 7. Estado RC (2026-07-29)

### Implementado

| Capa | Herramienta | Comando | Tests |
|------|-------------|---------|-------|
| Laravel | PHPUnit | `php artisan test` | Workflow CI |
| FastAPI unit | pytest | `pytest tests/unit/ -v` | 8 tests (dominio + crypto) |
| Móvil types | tsc | `npm run typecheck` | Workflow CI |
| Móvil unit | Jest | `npm test` | 4 tests (validador mensajes) |

### Script local

```powershell
.\scripts\verify-rc.ps1
```

### Pendiente post-RC

- FastAPI integración con PostgreSQL en CI (service container)
- Jest component tests (React Native Testing Library)
- E2E Detox flujo PIN → hábito
- Cobertura 80% dominio API (actual: subset unitario)
