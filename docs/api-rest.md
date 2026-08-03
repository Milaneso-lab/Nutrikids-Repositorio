# Reporte API NutriKids (FastAPI + PostgreSQL + SQLAlchemy)

## Arquitectura implementada

- `api/`: backend FastAPI con SQLAlchemy (PostgreSQL).
- `web1_flask/`: frontend Flask para padres.
- Laravel mantiene admin/nutriólogo y se configuró para consumir API por `FastApiClient`.

## Endpoints principales (Web1 + Web2)

- `POST /api/auth/login`
- `GET/POST /api/contactos`
- `GET/POST /api/comentarios`
- `GET/POST /api/discusiones`
- `GET/POST /api/usuarios` (admin)
- `GET/POST/PUT /api/pacientes` (admin/nutriólogo)
- `GET/POST/PUT /api/evaluaciones` (admin/nutriólogo)
- `GET/POST/PUT /api/menus` (admin/nutriólogo)
- `GET/POST /api/reportes` y `GET /api/reportes/{id}` (admin/nutriólogo)

## Variables importantes

- API:
  - `NUTRIKIDS_DATABASE_URL`
  - `NUTRIKIDS_SECRET_KEY`
- Laravel:
  - `NUTRIKIDS_API_BASE_URL`
  - `NUTRIKIDS_API_TIMEOUT`
- Flask:
  - `NUTRIKIDS_API_BASE_URL`
  - `FLASK_SECRET_KEY`

## Esquema PostgreSQL

- Script inicial: `api/sql/001_postgres_schema.sql`
