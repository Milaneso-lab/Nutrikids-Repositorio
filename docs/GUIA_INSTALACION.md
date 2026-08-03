# Guía de Instalación — NutriKids

## Requisitos

| Herramienta | Versión mínima |
|-------------|----------------|
| Docker Desktop | 4.x |
| Git | 2.x |
| Node.js (app móvil) | 20 LTS |
| PHP + Composer (dev Laravel local) | 8.2 / latest |
| Python (dev FastAPI local) | 3.10 |

## 1. Clonar repositorio

```powershell
git clone <url-repositorio> NutriKids
cd NutriKids
```

## 2. Variables de entorno

```powershell
copy .env.example .env
copy fastapi\.env.example fastapi\.env
```

Editar `.env` raíz:

| Variable | Descripción |
|----------|-------------|
| `POSTGRES_PASSWORD` | Contraseña PostgreSQL |
| `FLASK_SECRET_KEY` | Secreto sesión Flask (32+ chars) |
| `APP_KEY` | Generado con `php artisan key:generate` |

Editar `fastapi/.env`:

| Variable | Descripción |
|----------|-------------|
| `NUTRIKIDS_SECRET_KEY` | JWT signing (32+ chars aleatorios) |
| `NUTRIKIDS_DATABASE_URL` | En Docker se sobreescribe vía compose |
| `NUTRIKIDS_ENVIRONMENT` | `development` local, `production` en prod |

## 3. Docker Compose

```powershell
docker compose up -d --build
```

Esperar health checks (postgres, redis, fastapi, laravel, flask).

## 4. Migraciones

```powershell
docker compose exec laravel php artisan migrate --force
docker compose exec fastapi alembic upgrade head
```

## 5. Verificar servicios

| URL | Esperado |
|-----|----------|
| http://localhost:8000/health | `{"status":"ok","database":"ok",...}` |
| http://localhost:8080/up | 200 OK |
| http://localhost:5000/health | `{"status":"ok"}` |
| http://localhost:8000/docs | Swagger UI |

## 6. App móvil

```powershell
cd NutriKidsMovil
copy .env.example .env
npm install
npm start
```

- Emulador Android: `EXPO_PUBLIC_API_BASE_URL=http://10.0.2.2:8000`
- Dispositivo físico: IP LAN de tu PC

### Modo demo (sin API)

En `NutriKidsMovil/.env`:

```
EXPO_PUBLIC_DEMO_MODE=true
```

Credenciales demo: `demo@nutrikids.app` / `Demo1234`

## 7. Desarrollo local sin Docker (opcional)

### FastAPI

```powershell
cd fastapi
pip install -r requirements.txt
# PostgreSQL y Redis deben estar accesibles
uvicorn main:app --reload --port 8000
```

### Laravel

```powershell
composer install
php artisan serve --port=8080
```

### Flask

```powershell
cd flask
pip install -r requirements.txt
python app.py
```

## 8. Verificación RC

```powershell
.\scripts\verify-rc.ps1
```

## Solución de problemas

| Problema | Solución |
|----------|----------|
| FastAPI 503 en /health | Verificar postgres levantado y `NUTRIKIDS_DATABASE_URL` |
| Laravel 502 | Esperar `start_period` healthcheck; revisar logs `docker compose logs laravel` |
| Móvil no conecta API | Verificar URL, firewall, mismo WiFi |
| Seeds no aparecen | Solo en `development` con `NUTRIKIDS_ENABLE_DEV_SEED=true` |
