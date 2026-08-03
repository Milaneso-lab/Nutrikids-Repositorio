# Fase 6 — Portal Admin y autenticación unificada

**Fecha:** 2026-07-30

## Diagnóstico del sistema de autenticación

| Componente | Estado previo |
|------------|---------------|
| Login unificado Flask | Operativo vía `/login` → FastAPI valida → Laravel sesión para staff |
| Laravel AuthController | Funcional; redirect padre a `/` en lugar de `/portal` |
| JWT (FastAPI) | Access token 15 min para padres; staff usa sesión Laravel |
| RoleMiddleware | Básico; redirect incorrecto al denegar acceso |
| Seeders | Contraseñas antiguas (`admin123`, etc.) |
| Portal admin | Parcial (faltaban roles, permisos, bitácora, catálogos) |
| RBAC | Solo en FastAPI Alembic; Laravel usaba string `rol` |

**Base de datos:** PostgreSQL compartida (Docker). Los usuarios viven en tabla `usuarios` (compatible con MySQL en despliegues alternos).

## Cambios realizados

### Autenticación
- `LoginService` centraliza validación, sesión y redirects por rol.
- `AuthController` refactorizado; verifica `estado` si la columna existe.
- `RoleMiddleware` redirige al portal correcto del usuario autenticado.
- `PermissionMiddleware` + `PermissionService` para RBAC.
- Redirect padre corregido → `{FLASK}/portal`.
- Login Flask: botones demo y JS con credenciales Fase 6; logout → `/login`.

### Portal administrador
- **Roles** (`/admin/roles`)
- **Permisos** (`/admin/permisos`) con matriz editable si existen tablas RBAC
- **Bitácora** (`/admin/bitacora`) lee `ProyectoIA/DocumentacionProyecto/Bitacora.md`
- **Catálogos** (`/admin/catalogos`) hub hacia alimentos, recetas, menús
- Navegación admin ampliada

### Base de datos
- Migración `2026_07_30_190000_create_rbac_tables.php` (roles, permisos, rol_permiso, estado en usuarios)
- `RolesPermisosSeeder` sincroniza desde `config/nutrikids.php`
- `CredencialesSeeder` actualizado con contraseñas Fase 6
- `DatabaseSeeder` sin `AdminTemporalSeeder` duplicado
- `fastapi/seed.py` actualiza contraseñas en usuarios existentes

## Archivos creados

```
config/nutrikids.php
app/Services/Auth/LoginService.php
app/Services/Rbac/PermissionService.php
app/Http/Middleware/PermissionMiddleware.php
app/Models/Role.php
app/Models/Permiso.php
app/Http/Controllers/Admin/RolController.php
app/Http/Controllers/Admin/PermisoController.php
app/Http/Controllers/Admin/BitacoraController.php
app/Http/Controllers/Admin/CatalogoController.php
database/migrations/2026_07_30_190000_create_rbac_tables.php
database/seeders/RolesPermisosSeeder.php
resources/views/admin/roles/index.blade.php
resources/views/admin/permisos/index.blade.php
resources/views/admin/bitacora/index.blade.php
resources/views/admin/catalogos/index.blade.php
docs/fase6-auth-admin-portal.md
```

## Archivos modificados

```
app/Http/Controllers/AuthController.php
app/Http/Middleware/RoleMiddleware.php
app/Models/User.php
bootstrap/app.php
config/services.php
routes/web.php
database/seeders/CredencialesSeeder.php
database/seeders/DatabaseSeeder.php
resources/views/admin/partials/navigation.blade.php
flask/app.py
flask/templates/login_full.html
fastapi/seed.py
ProyectoIA/DocumentacionProyecto/Bitacora.md
```

## Usuarios de prueba (seeders)

| Rol | Email | Contraseña |
|-----|-------|------------|
| Administrador | admin@nutrikids.com | Admin123* |
| Nutriólogo | nutriologo@nutrikids.com | Nutri123* |
| Padre | padre@nutrikids.com | Padre123* |

## Comandos de despliegue

```bash
docker compose exec laravel php artisan migrate --force
docker compose exec laravel php artisan db:seed --class=CredencialesSeeder --force
docker compose exec laravel php artisan db:seed --class=RolesPermisosSeeder --force
docker compose restart fastapi   # reaplica seed.py
```

## Flujo de login

1. Usuario accede a `http://localhost:5000/login` (Flask — login único).
2. Flask valida credenciales contra FastAPI `/api/auth/login`.
3. **Padre:** JWT en sesión Flask → redirect `/portal`.
4. **Admin/Nutriólogo:** segundo POST a Laravel `/IniciarSesion` → cookie de sesión → dashboard correspondiente.

## Recomendaciones — siguiente fase

1. Migrar Flask a `/api/v1/auth/login` (refresh tokens + logout con revocación).
2. Unificar auditoría Laravel con `security_audit_logs` de FastAPI.
3. Persistir instituciones en BD compartida.
4. Tests E2E de login por rol (Playwright o pytest).
5. Middleware `permission:` en rutas admin sensibles.
