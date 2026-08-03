# NutriKids

Plataforma integral para la prevención y acompañamiento de la obesidad infantil: portal público, backoffice clínico, API REST unificada y aplicación móvil gamificada para niños.

## Stack

| Componente | Tecnología | Puerto |
|------------|------------|--------|
| API REST | FastAPI (Python 3.10) | 8000 |
| Backoffice admin/nutriólogo | Laravel 12 (PHP 8.2+) | 8080 |
| Portal público | Flask (Python) | 5000 |
| Base de datos | PostgreSQL 15 | 5432 |
| Caché / rate limit | Redis 7 | — |
| App móvil | Expo SDK 57 / React Native | — |

## Inicio rápido (Docker)

```powershell
# 1. Copiar variables de entorno
copy .env.example .env
copy fastapi\.env.example fastapi\.env

# 2. Editar .env — definir POSTGRES_PASSWORD, FLASK_SECRET_KEY, NUTRIKIDS_SECRET_KEY

# 3. Levantar servicios
docker compose up -d --build

# 4. Migraciones
docker compose exec laravel php artisan migrate --force
docker compose exec fastapi alembic upgrade head
```

Servicios disponibles:

- API: http://localhost:8000/docs
- Laravel: http://localhost:8080
- Flask: http://localhost:5000
- Health API: http://localhost:8000/health

## App móvil

```powershell
cd NutriKidsMovil
copy .env.example .env
npm install
npm start
```

Para demostración sin API: `EXPO_PUBLIC_DEMO_MODE=true` en `.env`.

## Verificación RC

```powershell
.\scripts\verify-rc.ps1
```

Ejecuta PHPUnit (Laravel), pytest unit (FastAPI), typecheck y tests unitarios (móvil).

## Documentación

| Documento | Ubicación |
|-----------|-----------|
| Estado vivo del proyecto | `ProyectoIA/DocumentacionProyecto/EstadoProyecto.md` |
| Arquitectura | `ProyectoIA/DocumentacionProyecto/02_Arquitectura.md` |
| API | `ProyectoIA/DocumentacionProyecto/04_API.md` |
| App móvil | `ProyectoIA/DocumentacionProyecto/07_AppMovil.md` |
| Docker | `ProyectoIA/DocumentacionProyecto/08_Docker.md` |
| Release Candidate | `ProyectoIA/DocumentacionProyecto/17_ReleaseCandidate.md` |
| Guía de instalación | `docs/GUIA_INSTALACION.md` |
| Guía de despliegue | `docs/GUIA_DESPLIEGUE.md` |
| Guía desarrolladores | `docs/GUIA_DESARROLLADORES.md` |
| Manual técnico | `docs/MANUAL_TECNICO.md` |
| Manual de usuario | `docs/MANUAL_USUARIO.md` |
| Roadmap | `ROADMAP.md` |

## Seguridad

- JWT 15 min + refresh con rotación (FastAPI `/api/v1/auth/*`)
- RBAC por permisos
- Seeds de desarrollo desactivados en `production`/`staging`
- Modo demo móvil **desactivado por defecto** en RC (`EXPO_PUBLIC_DEMO_MODE=false`)

Ver `README_SECURITY.md` y `ProyectoIA/DocumentacionProyecto/05_Seguridad.md`.

## Licencia

Proyecto académico — consultar repositorio para términos de uso.
