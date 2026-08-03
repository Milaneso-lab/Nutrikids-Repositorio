# Credenciales de prueba (NutriKids — Fase 6)

## Login unificado

**URL:** `http://localhost:5000/login` (Flask)

Todos los perfiles usan el mismo formulario: correo + contraseña.

| Rol | Correo | Contraseña | Destino tras login |
|-----|--------|------------|-------------------|
| **Administrador** | `admin@nutrikids.com` | `Admin123*` | `http://localhost:8080/admin/dashboard` |
| **Nutriólogo** | `nutriologo@nutrikids.com` | `Nutri123*` | `http://localhost:8080/nutriologo/dashboard` |
| **Padre** | `padre@nutrikids.com` | `Padre123*` | `http://localhost:5000/portal` |

## Seeders automatizados

```bash
docker compose exec laravel php artisan db:seed --class=RolesPermisosSeeder --force
docker compose exec laravel php artisan db:seed --class=CredencialesSeeder --force
```

FastAPI sincroniza los mismos usuarios al iniciar (`fastapi/seed.py`).

## Flujo técnico

1. Flask valida contra FastAPI (`/api/auth/login`).
2. **Padre:** JWT en sesión Flask → portal `/portal`.
3. **Admin / Nutriólogo:** segundo POST a Laravel `/IniciarSesion` → sesión web → dashboard correspondiente.

## Seguridad

- Contraseñas hasheadas (bcrypt / Laravel Hash).
- JWT para padres (15 min access token en API v1).
- Sesión Laravel en base de datos para staff.
- Middleware `role:admin` y `role:nutriologo` protegen rutas.

## Documentación relacionada

- `docs/fase6-auth-admin-portal.md`
