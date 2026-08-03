# Esquema integrado NutriKids (PostgreSQL 15)

Una sola base de datos `nutrikids` compartida por **Laravel** (admin / nutriólogos), **FastAPI** (API REST, JWT, padres + nutriólogo) y **Flask** (opcional `SELECT 1` vía SQLAlchemy; la lógica de padres usa la API).

Los nombres de tabla están en **minúsculas** (`usuarios`, `discusiones`, `contactos`) para evitar conflictos entre MySQL (case‑insensible) y PostgreSQL.

## Tablas y uso por framework

| Tabla | Laravel | FastAPI | Flask directo |
|-------|---------|---------|----------------|
| `usuarios` | Autenticación web, panel, Eloquent `User` | Login/register JWT, modelos `Usuario` | Solo comprobación BD opcional |
| `contactos` | Modelo `Contacto`, formularios | CRUD público | Proxy HTTP |
| `comentarios` | Modelo `Comentario` | CRUD + `id_usuario` | Proxy HTTP |
| `discusiones` | Modelo `Discusion` | CRUD foros | Proxy HTTP |
| `pacientes` | Modelo `Paciente` | API nutriólogo | — |
| `evaluaciones` | Modelo `Evaluacion` | API | — |
| `menus` | Modelo `Menu` | API | — |
| `reportes` | Modelo `Reporte` | API | — |
| `infantes` | Modelo `Infante` | Modelo ORM | — |
| `citas` | Modelo `Cita` | Modelo ORM | — |
| `alertas` | Modelo `Alerta` | Modelo ORM | — |
| `alergias` | Modelo `Alergia` | Modelo ORM | — |
| `notas_nutriologo` | Modelo `NotaNutriologo` | Modelo ORM | — |
| `menus_semanales` | Modelo `MenuSemanal` | Modelo ORM | — |
| `password_reset_tokens` | Laravel estándar | Modelo ORM | — |
| `cache`, `cache_locks` | Laravel cache DB | Modelo ORM | — |
| `jobs`, `job_batches`, `failed_jobs` | Colas Laravel | Modelo ORM | — |
| `sessions` | Sesiones Laravel (`SESSION_DRIVER=database`) | Modelo ORM | — |

## Relaciones importantes

- `comentarios.id_usuario` → `usuarios.id_usuario` (ON DELETE CASCADE)
- `discusiones.id_usuario` → `usuarios.id_usuario` (ON DELETE CASCADE)
- `evaluaciones.paciente_id` → `pacientes.id` (ON DELETE SET NULL)
- `menus.paciente_id` → `pacientes.id` (ON DELETE SET NULL)
- `reportes.paciente_id` → `pacientes.id` (ON DELETE SET NULL)

## DDL de referencia

- Migraciones oficiales: `database/migrations/*.php` (se aplican con `php artisan migrate` en el contenedor Laravel).
- Resumen SQL estático: `sql/schema_postgres.sql` (puede diferir si las migraciones evolucionan; prevalece **Laravel**).

## Arranque con Docker y datos limpios

Si ya tenías un volumen de Postgres con un esquema antiguo (por ejemplo tablas `Usuarios` y `usuarios` a la vez), elimina el volumen y vuelve a crear la base:

```bash
docker compose down -v
docker compose up -d
```

Esto ejecuta de nuevo todas las migraciones Laravel sobre Postgres vacío. La API FastAPI inserta tres usuarios de demo si faltan (`admin@nutrikids.com`, etc.).

## Versiones detectadas en el repositorio

- **Laravel**: `^12.0` (`composer.json`), PHP `^8.2`
- **Flask**: 3.x (`web1_flask/requirements.txt`)
- **FastAPI**: 0.115.x (`fastapi_app/requirements.txt`)
