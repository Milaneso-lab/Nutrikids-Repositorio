# Alembic — Migraciones de base de datos NutriKids

> **Dueño del esquema de negocio** según ADR-003 (`14_DecisionesArquitectura.md`).
> Las migraciones Laravel en `database/migrations/` crean el baseline; Alembic aplica el esquema objetivo de `03_BaseDatos.md`.

## Requisitos

- PostgreSQL accesible (Docker Compose o local)
- Variables en `fastapi/.env` o entorno: `NUTRIKIDS_DATABASE_URL`

## Comandos

```bash
cd fastapi

# Aplicar todas las migraciones
python -m alembic upgrade head

# Ver estado
python -m alembic current

# Revertir última migración
python -m alembic downgrade -1
```

## Orden de despliegue

1. `php artisan migrate` (Laravel — baseline + tablas de framework)
2. `python -m alembic upgrade head` (FastAPI — esquema objetivo)

## Revisiones

| Revisión | Descripción |
|---|---|
| 20260728_0001 | Baseline Laravel (no-op) |
| 20260728_0002 | RBAC: roles, permisos, extensión usuarios |
| 20260728_0003 | Tabla `ninos`, `nino_credenciales`, migración desde `pacientes` |
| 20260728_0004 | `refresh_tokens` |
| 20260728_0005 | Dominio clínico completo + `menu_items` |
| 20260728_0006 | `citas.nino_id`, restricciones comunidad |
| 20260728_0007 | Gamificación completa + semillas de catálogo |

## Tablas legacy conservadas

`pacientes` e `infantes` **no se eliminan** en esta fase (ver `16_PlanModernizacion.md` §Paso 5).
Las columnas `paciente_id` en evaluaciones/menús/reportes se conservan para compatibilidad con código existente.
