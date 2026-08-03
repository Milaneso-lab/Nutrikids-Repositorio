# Manual Técnico — NutriKids

## 1. Arquitectura

Ver [`ARQUITECTURA_FINAL.md`](./ARQUITECTURA_FINAL.md) y `ProyectoIA/DocumentacionProyecto/02_Arquitectura.md`.

## 2. Base de datos

- **Motor:** PostgreSQL 15
- **Esquema objetivo:** Alembic (`fastapi/alembic/versions/`) — 8 revisiones baseline
- **Legacy:** migraciones Laravel coexisten durante transición
- **Redis:** rate limiting JWT, denylist, caché futura

Detalle tablas: `ProyectoIA/DocumentacionProyecto/03_BaseDatos.md`.

## 3. API REST

| Prefijo | Uso |
|---------|-----|
| `/api/v1/auth/*` | Login, refresh, registro, logout |
| `/api/v1/users/*` | Perfil usuario |
| `/api/v1/ninos/*` | CRUD niños (padres) |
| `/api/v1/habitos/*` | Catálogo y registros |
| `/api/v1/...` | Dominio clínico, comunidad, citas |

OpenAPI: http://localhost:8000/docs

Contrato completo: `04_API.md`.

## 4. Autenticación

- Access token JWT: 15 min
- Refresh token: rotación, almacenado hasheado
- Móvil: SecureStore (expo-secure-store)
- Web Laravel: sesión PHP (transición a API auth pendiente)

## 5. App móvil — módulos

| Módulo | Ruta | Descripción |
|--------|------|-------------|
| auth | `features/auth/` | Login, registro, bienvenida |
| familia | `features/familia/` | Dashboard padre, CRUD niños |
| nino | `features/nino/` | Modo niño, home infantil |
| progresion | `features/progresion/` | XP, niveles, mascota, misiones |
| habitos | `features/habitos/` | Tracker, calendario, stats |
| comunicacion | `features/comunicacion/` | Notificaciones, mensajes, push infra |

Providers globales: `AppProviders.tsx` (Auth, Progression, Communication, Push).

## 6. Docker

Servicios: postgres, redis, laravel, fastapi, flask.

Health checks en todos los servicios expuestos.

## 7. Testing

| Capa | Herramienta | Ubicación |
|------|-------------|-----------|
| Laravel | PHPUnit | `tests/` |
| FastAPI unit | pytest | `fastapi/tests/unit/` |
| FastAPI integration | pytest + TestClient | `fastapi/tests/integration/` (requiere BD) |
| Móvil unit | Jest | `NutriKidsMovil/src/**/__tests__/` |
| Móvil types | tsc | `npm run typecheck` |

## 8. Seguridad

- CORS configurable (`NUTRIKIDS_CORS_ORIGINS`)
- Rate limiting global y por endpoint login
- Headers seguridad en FastAPI y Flask
- Seeds dev bloqueados en production/staging
- Política contraseñas bcrypt ≥12 rounds

Ver `05_Seguridad.md`.

## 9. Decisiones arquitectura

Registro ADR: `14_DecisionesArquitectura.md`.

## 10. Limitaciones conocidas RC

- Login PIN niño (T4.3) no implementado
- Progresión parcialmente local (AsyncStorage) hasta endpoints API completos
- Push notifications: infra local, worker remoto pendiente
- Tablas clínicas placeholder en BD
