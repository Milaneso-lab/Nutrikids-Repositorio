# 08 — Infraestructura Docker Objetivo

> Depende de: [`01_AuditoriaProyecto.md`](./01_AuditoriaProyecto.md) §3 y §6 (estado real del `docker-compose.yml` actual), [`02_Arquitectura.md`](./02_Arquitectura.md), [`05_Seguridad.md`](./05_Seguridad.md) §3.
> No se modifica el `docker-compose.yml` real en este documento — se especifica el objetivo para que la implementación lo traduzca.

---

## 1. Contenedores objetivo

| Contenedor | Base | Responsabilidad | Expuesto a Internet |
|---|---|---|---|
| `gateway` | nginx / traefik | TLS termination, reverse proxy, balanceo entre réplicas de `api`, rate limit L7 | **Sí** (único punto de entrada público) |
| `laravel` | php-apache (ya existe) | Backoffice admin/nutriólogo | No directo — vía `gateway` |
| `web` (Flask hoy, Next.js fase 2) | python / node | Sitio público de padres | No directo — vía `gateway` |
| `api` (N réplicas) | python (uvicorn) | Lógica de negocio, único dueño de datos | No — solo interno, alcanzado vía `gateway` |
| `postgres` | postgres:15-alpine (ya existe) | Base de datos transaccional | **No** — corrige exposición actual (`5432:5432`) |
| `redis` | redis (nuevo) | Cache, rate limiting distribuido, denylist JWT | No |
| `worker` (nuevo, fase de mensajería) | python (mismo código que `api`) | Procesamiento asíncrono (notificaciones push, recálculo de puntos en lote) | No |
| `prometheus` / `grafana` / `loki` (nuevos, fase de madurez) | oficiales | Observabilidad | No (acceso solo por VPN/túnel administrativo) |

## 2. Red y comunicación

Se mantiene el patrón ya correcto del `docker-compose.yml` actual: una única red `app-network` tipo bridge, con `depends_on` + `healthcheck` por servicio (ya implementado hoy para `postgres`→`laravel`→`fastapi`→`flask`, se conserva y se extiende a `redis` y `gateway`).

```
Internet ──▶ gateway (443/80) ──┬──▶ laravel (interno)
                                  ├──▶ web (interno)
                                  └──▶ api ×N (interno, balanceado)
                                            │
                          ┌──────────────────┼──────────────────┐
                          ▼                  ▼
                     postgres (interno)   redis (interno)
```

Ningún servicio backend publica puertos al host salvo `gateway`. Esto corrige directamente el hallazgo de auditoría de Postgres expuesto en `5432:5432`.

## 3. Volúmenes

Se conserva el patrón de hot-reload ya usado hoy (montaje de código fuente para `laravel`, `fastapi`, `flask` sin rebuild) para entornos de desarrollo. En producción, **se elimina el bind-mount de código** y se usa build de imagen inmutable (ver `11_Deployment.md`) — el hot-reload es exclusivamente una comodidad de desarrollo local, nunca un patrón de producción.

Volumen persistente `postgres_data` (ya existe) se conserva; se añade volumen persistente para `redis` solo si se usa como almacén de colas (no necesario si Redis se usa puramente como cache/rate-limit, que es efímero por naturaleza).

## 4. Healthchecks

Se generaliza el patrón ya bien implementado (`docker-compose.yml` actual tiene healthchecks correctos para los 4 servicios existentes) a los nuevos contenedores: `redis` (`redis-cli ping`), `gateway` (chequeo de `/health` proxied hacia `api`), `api` (ya expone `/health`, se reutiliza).

## 5. Variables de entorno y secretos

- Ningún valor sensible fijo en `docker-compose.yml` (corrige hallazgo de auditoría) — se inyectan vía `env_file` apuntando a `.env` no versionado, y en producción vía el gestor de secretos del orquestador/cloud (`05_Seguridad.md` §5).
- Un único `.env.example` por servicio, consistente entre sí (corrige la inconsistencia de puerto de Flask detectada en la auditoría) — tarea de limpieza concreta en `16_PlanModernizacion.md`.

## 6. Entornos

| Entorno | Orquestación | Diferencia clave |
|---|---|---|
| Desarrollo local | `docker-compose.yml` (el actual, evolucionado) | Bind-mounts de código, `APP_DEBUG=true`, sin TLS (localhost) |
| Staging | Mismo compose o equivalente gestionado (ver `09_Cloud.md`) | Imágenes inmutables, datos de prueba, TLS con certificado de staging |
| Producción | Orquestador gestionado (ver `09_Cloud.md`) | Imágenes inmutables versionadas, múltiples réplicas de `api`, secretos gestionados, backups automáticos |

## 7. Qué no cambia

- Se mantiene Docker Compose como base para desarrollo local en todas las fases — no se impone Kubernetes salvo que `09_Cloud.md` determine que el volumen de tráfico lo justifica.
- Los Dockerfiles ya existentes (`Dockerfile.laravel`, `Dockerfile.fastapi`, `Dockerfile.flask`) se mantienen como base y se ajustan incrementalmente (multi-stage build para imágenes de producción más pequeñas es una mejora recomendada, no un rediseño).
