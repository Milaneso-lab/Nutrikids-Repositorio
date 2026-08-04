# Reglas de firewall y exposición de puertos — NutriKids

## Modelo de seguridad

| Capa | Componentes | Acceso |
|------|-------------|--------|
| **Público** | Nginx gateway (HTTPS 9443), Flask, Laravel, FastAPI vía Railway | Internet / evaluadores |
| **Privado** | PostgreSQL, Redis | Solo red Docker interna (`private-network`) |
| **Administración** | pgAdmin, Prometheus, Grafana | Solo `127.0.0.1` en el host |

## Puertos permitidos (desarrollo local con Docker)

| Puerto | Servicio | Binding | Motivo |
|--------|----------|---------|--------|
| 9443 | Nginx HTTPS (gateway) | 0.0.0.0 | Entrada pública segura con balanceador |
| 9080 | Nginx HTTP → redirect HTTPS | 0.0.0.0 | Redirección a TLS |
| 5000 | Flask (sitio padres) | 0.0.0.0 | Demo directa sin gateway |
| 8080 | Laravel (admin/nutriólogo) | 0.0.0.0 | Panel administrativo |
| 8000 | FastAPI (API) | 0.0.0.0 | Clientes móviles / pruebas |
| 9090 | Prometheus | 127.0.0.1 | Monitoreo interno |
| 3000 | Grafana | 127.0.0.1 | Dashboards internos |
| 5050 | pgAdmin | 127.0.0.1 | Administración BD local |
| 5432 | PostgreSQL | 127.0.0.1 | Herramientas locales (DBeaver, etc.) |

## Puertos bloqueados / no expuestos

| Servicio | Motivo |
|----------|--------|
| Redis (6379) | Cache/sessions; solo accesible dentro de Docker |
| PostgreSQL desde Internet | BD en red privada; Railway usa red interna del proveedor |
| fastapi-b | Réplica interna del balanceador; sin puerto en el host |

## Producción en Railway

- TLS lo termina **Railway** (certificado válido automático).
- PostgreSQL y Redis **no** tienen IP pública en Railway.
- URLs públicas actuales del proyecto:
  - FastAPI: `https://nutrikids-sitioweb.up.railway.app`
  - Flask: `https://nutrikids-flask-production.up.railway.app`
  - Laravel: `https://nutrikids-laravel-production.up.railway.app`

## Comandos útiles (Windows / Linux)

Ver puertos en escucha:

```bash
netstat -an | findstr LISTENING
```

Levantar stack con gateway + monitoreo:

```bash
docker compose -f docker-compose.yml -f docker-compose.infra.yml --profile gateway --profile monitoring up -d
```
