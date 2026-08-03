# Guía de Despliegue — NutriKids

## Entornos

| Entorno | `NUTRIKIDS_ENVIRONMENT` | Seeds dev | Demo móvil |
|---------|-------------------------|-----------|------------|
| development | development | permitidos | opcional |
| staging | staging | **no** | no |
| production | production | **no** | **no** |

## Checklist pre-despliegue

- [ ] Rotar `NUTRIKIDS_SECRET_KEY`, `FLASK_SECRET_KEY`, `POSTGRES_PASSWORD`
- [ ] `NUTRIKIDS_ENABLE_DEV_SEED=false` o entorno `production`
- [ ] `EXPO_PUBLIC_DEMO_MODE=false` en build móvil
- [ ] PostgreSQL no expuesto a internet (solo red interna)
- [ ] Backups PostgreSQL configurados
- [ ] TLS terminado en gateway (pendiente T1.4 — usar reverse proxy externo mientras tanto)

## Docker Compose (servidor único)

```bash
docker compose -f docker-compose.yml up -d --build
docker compose exec laravel php artisan migrate --force
docker compose exec fastapi alembic upgrade head
```

### Variables críticas producción

```env
NUTRIKIDS_ENVIRONMENT=production
NUTRIKIDS_ENABLE_DEV_SEED=false
NUTRIKIDS_SKIP_CREATE_ALL=1
APP_ENV=production
APP_DEBUG=false
FLASK_COOKIE_SECURE=true
```

## Orden de arranque

1. PostgreSQL + Redis
2. FastAPI (no depende de Laravel)
3. Laravel
4. Flask

El `docker-compose.yml` RC refleja este orden.

## Health checks

| Servicio | Endpoint |
|----------|----------|
| FastAPI | `GET /health` → 200 si BD ok, 503 si degradado |
| Laravel | `GET /up` |
| Flask | `GET /health` |
| Postgres | `pg_isready` |
| Redis | `redis-cli ping` |

## Migraciones

- **Esquema negocio:** Alembic (`fastapi/alembic/`) — fuente de verdad objetivo
- **Laravel:** migraciones históricas + compatibilidad

Ejecutar siempre `alembic upgrade head` antes de exponer tráfico.

## App móvil (EAS Build)

```powershell
cd NutriKidsMovil
# Configurar EXPO_PUBLIC_API_BASE_URL=https://api.tudominio.com
eas build --platform android
eas build --platform ios
```

## Rollback

1. Detener tráfico en gateway
2. `docker compose down`
3. Restaurar snapshot PostgreSQL
4. Desplegar imagen/tag anterior
5. Verificar `/health` en todos los servicios

## Monitoreo recomendado (post-RC)

- Uptime en `/health` de FastAPI y Flask
- Logs centralizados (JSON structured — pendiente implementación)
- Alertas disco PostgreSQL y Redis memory

Ver también `ProyectoIA/DocumentacionProyecto/11_Deployment.md` y `09_Cloud.md`.
