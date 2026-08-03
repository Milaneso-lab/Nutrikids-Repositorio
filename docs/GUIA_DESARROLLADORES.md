# Guía para Desarrolladores — NutriKids

## Estructura del monorepo

```
NutriKids/
├── app/                    # Laravel — controllers, models, views
├── fastapi/                # API REST — routers, app/api/v1, alembic
├── flask/                  # Portal público
├── NutriKidsMovil/         # App Expo / React Native
├── docker-compose.yml
├── ProyectoIA/DocumentacionProyecto/  # Documentación canónica
└── docs/                   # Guías operativas
```

## Convenciones

### API (FastAPI)

- Contrato nuevo: `/api/v1/*`
- Legacy deprecado: `/api/*` (mantener compatibilidad temporal)
- Auth: `POST /api/v1/auth/login`, refresh en `/api/v1/auth/refresh`
- RBAC: permisos en token + middleware

### App móvil

- **Feature First:** `src/features/<dominio>/`
- Capas: `domain/`, `services/`, `repositories/`, `hooks/`, `components/`, `screens/`
- Alias TypeScript: `@features/*`, `@core/*`, etc.
- Estado global: Zustand en `@state/`
- TypeScript estricto — `npm run typecheck` debe pasar

### Laravel

- Roles: `admin`, `nutriologo`, `usuario`
- Middleware `RoleMiddleware`

## Flujo de trabajo

1. Leer `EstadoProyecto.md` y `13_Backlog.md`
2. Crear rama feature
3. Implementar + tests
4. Actualizar documentación afectada
5. `.\scripts\verify-rc.ps1` antes de PR

## Comandos útiles

```powershell
# Stack completo
docker compose up -d

# FastAPI tests unitarios (sin BD)
cd fastapi
$env:NUTRIKIDS_ENABLE_DEV_SEED="false"
python -m pytest tests/unit/ -v

# Móvil
cd NutriKidsMovil
npm run typecheck
npm test

# Laravel
php artisan test
```

## Añadir endpoint API

1. Modelo/schema en `fastapi/app/`
2. Router en `fastapi/app/api/v1/`
3. Registrar en `fastapi/app/api/v1/router.py`
4. Documentar en `04_API.md`
5. Test unitario o integración

## Añadir feature móvil

1. Carpeta en `src/features/<nombre>/`
2. Pantallas + navegación en `src/navigation/`
3. Provider si requiere bootstrap global
4. Documentar en `07_AppMovil.md`

## Modo demo

Solo para demos y desarrollo UI sin backend:

```
EXPO_PUBLIC_DEMO_MODE=true
```

**No usar en builds de producción.**

## CI

GitHub Actions (`.github/workflows/tests.yml`):

- Laravel PHPUnit (PHP 8.2–8.4)
- FastAPI unit tests
- Móvil typecheck + Jest

## Documentación IA

Ver `ProyectoIA/DocumentacionProyecto/README_IA.md` y `FlujoTrabajoIA.md`.
