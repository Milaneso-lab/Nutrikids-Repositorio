# 09 — Estrategia de Nube, Balanceo y Escalabilidad

> Depende de: [`08_Docker.md`](./08_Docker.md), [`02_Arquitectura.md`](./02_Arquitectura.md).

---

## 1. Enfoque por madurez, no "AWS desde el día 1"

El proyecto nace en contexto académico/early-stage. Se define una progresión de dos etapas para no incurrir en costo/complejidad operativa prematura, con criterios de disparo explícitos para pasar de una a otra.

### Etapa 1 — PaaS de bajo costo (lanzamiento y validación)
**Proveedor recomendado**: Railway, Render o Fly.io (equivalentes; decisión final según costo/región en el momento de contratar).

Justificación: despliegue de contenedores Docker existentes casi sin cambios, Postgres gestionado incluido, TLS automático, sin necesidad de gestionar VMs, escalado vertical simple con un clic. Adecuado mientras el tráfico y el equipo son pequeños.

### Etapa 2 — Cloud empresarial (cuando se cumplan criterios de escala)
**Proveedor recomendado**: AWS (alternativa equivalente: GCP), con:
- **ECS Fargate** (o equivalente) para `api`, `laravel`, `web` — contenedores sin gestión de servidores.
- **RDS PostgreSQL** (con réplica de lectura cuando el criterio de §3 se cumpla).
- **ElastiCache Redis** para cache/rate-limit distribuido.
- **S3 + CloudFront** para assets estáticos y build de Next.js.
- **ALB (Application Load Balancer)** como balanceador de capa 7 delante de `api`.

**Criterios de disparo para pasar de Etapa 1 a Etapa 2** (cualquiera de estos, no todos):
- Tráfico sostenido que exige más de 2 réplicas de la API de forma constante.
- Requisito contractual/institucional de SLA formal o de residencia de datos específica.
- Necesidad de multi-región o de cumplimiento más estricto que lo que el PaaS ofrece.

## 2. Balanceo de carga

- **Capa 7** (HTTP): Nginx/Traefik en Etapa 1 (dentro del propio compose, `08_Docker.md` §1) o ALB gestionado en Etapa 2. Balancea entre réplicas de `api`, que son **stateless por diseño** (`02_Arquitectura.md` §3.1) — requisito indispensable para que el balanceo funcione sin *sticky sessions*.
- Health checks activos (`GET /health`, ya existente) determinan qué réplica recibe tráfico.
- Laravel y `web` (Flask/Next.js) no necesitan balanceo horizontal en Etapa 1 (tráfico interno/público bajo respectivamente), se revisita si el criterio de §1 se cumple para ellos específicamente.

## 3. Escalabilidad de base de datos

- Vertical primero (aumentar recursos de la instancia gestionada) — más simple y suficiente para el volumen esperado en Etapa 1 y buena parte de Etapa 2.
- Réplica de lectura quando reportes/analítica compitan de forma medible con las escrituras transaccionales (monitoreado vía métricas de `09_Cloud.md`/Prometheus, no una decisión anticipada sin datos).
- Particionado de tablas de alto volumen (`habito_registros`, `alertas` — identificadas en `03_BaseDatos.md` §8) solo cuando el tamaño de tabla lo justifique en métricas reales, no de antemano.

## 4. Firewall y seguridad de red en la nube

- Security groups con regla deny-all por defecto; solo el gateway/ALB expuesto en 443 (y 80→443 redirect).
- Postgres, Redis, y las réplicas de `api` en subred privada, sin IP pública, alcanzables solo desde la VPC.
- Acceso administrativo (SSH/consola de gestión) restringido por IP o vía bastión/VPN — nunca abierto a `0.0.0.0/0`.
- WAF (AWS WAF o Cloudflare delante del gateway) recomendado antes de escalar a más instituciones/usuarios, no obligatorio en Etapa 1.

## 5. Continuidad de negocio (backups y recuperación)

- Backups automáticos diarios de RDS/Postgres gestionado con retención ≥30 días (heredado de `05_Seguridad.md` §9).
- Objetivo inicial (a formalizar con el negocio cuando exista producción real): RPO ≤ 24h, RTO ≤ 4h. Se revisa al escalar.
- Simulacro de restauración documentado al menos una vez antes de considerar el entorno "producción real" (no solo demo).

## 6. Costos — filosofía

Cada componente añadido a la infraestructura (réplica de lectura, WAF, multi-región, Kubernetes) debe justificarse con un criterio de escala observado, no con anticipación especulativa — evita gasto y complejidad operativa prematuros en un proyecto que hoy es de bajo volumen. Esta filosofía es la misma aplicada en `02_Arquitectura.md` §8 ("Qué NO cambia en el corto plazo").
