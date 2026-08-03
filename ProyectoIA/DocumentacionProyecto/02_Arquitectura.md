# 02 — Arquitectura Objetivo

> Depende de: [`01_AuditoriaProyecto.md`](./01_AuditoriaProyecto.md). Justificaciones tecnológicas detalladas en [`14_DecisionesArquitectura.md`](./14_DecisionesArquitectura.md).

---

## 1. Principio arquitectónico central

**API-first, multi-cliente.** Existe una única API REST (FastAPI) que concentra toda la lógica de negocio y el acceso a datos. Todos los clientes —panel admin/nutriólogo, sitio público de padres y app móvil de niños— son consumidores de esa API y **no acceden a la base de datos directamente ni duplican reglas de negocio**.

Esto resuelve el hallazgo #1 de la auditoría (autenticación duplicada Laravel/FastAPI): en el estado objetivo, **solo FastAPI verifica credenciales y emite tokens**. Laravel deja de tener su propia lógica de `password_verify`.

---

## 2. Diagrama de arquitectura objetivo

```
                                   ┌─────────────────────────────┐
                                   │        Clientes              │
                                   │                               │
   ┌───────────────┐   ┌───────────────┐   ┌────────────────────┐
   │ Web Admin /    │   │  Web Pública   │   │   App Móvil (RN)   │
   │ Nutriólogo     │   │  (Padres)      │   │   (Niños)          │
   │ Laravel Blade  │   │  Flask→Next.js │   │  React Native      │
   │ (backoffice)   │   │  (fase 2)      │   │                     │
   └───────┬────────┘   └───────┬────────┘   └──────────┬─────────┘
           │ HTTPS + JWT        │ HTTPS + JWT/cookie      │ HTTPS + JWT
           └────────────────────┼─────────────────────────┘
                                 ▼
                    ┌───────────────────────────┐
                    │   API Gateway / Reverse     │  ← Nginx / Traefik
                    │   Proxy + Balanceador       │     TLS termination, rate limit L7
                    └─────────────┬──────────────┘
                                  ▼
                    ┌───────────────────────────┐
                    │      API REST (FastAPI)     │  ← única fuente de lógica
                    │  Auth · Pacientes · Citas    │     de negocio y de datos
                    │  Evaluaciones · Menús ·      │
                    │  Reportes · Gamificación     │
                    │  (N réplicas sin estado)     │
                    └──────┬──────────────┬───────┘
                            │              │
                            ▼              ▼
                 ┌───────────────┐  ┌──────────────┐
                 │  PostgreSQL    │  │    Redis      │
                 │  (datos)       │  │  cache · rate  │
                 │  primaria +    │  │  limit ·       │
                 │  réplica lectura│  │  sesiones/JWT  │
                 └───────────────┘  │  denylist ·    │
                                    │  colas (futuro)│
                                    └──────────────┘

        Observabilidad transversal: Prometheus + Grafana + Loki + Sentry
```

---

## 3. Responsabilidades por módulo

### 3.1 API REST central (FastAPI)
- **Única** responsable de: autenticación (login, registro, refresh, logout/revocación), autorización (RBAC), reglas de negocio (citas, evaluaciones, menús, reportes, gamificación), validación de datos de entrada (Pydantic), acceso a PostgreSQL y Redis.
- Expone versión de contrato estable (`/api/v1/...`, ver `04_API.md`).
- No renderiza HTML. No conoce la existencia de Blade/Jinja/React Native — responde JSON exclusivamente.
- Corre **sin estado** (stateless): cualquier instancia puede atender cualquier petición → requisito para poder escalar horizontalmente detrás de un balanceador.

### 3.2 Web Admin / Nutriólogo (Laravel, se mantiene)
- Backoffice interno para roles `admin` y `nutriologo`. Bajo riesgo de exposición pública, tráfico bajo, no necesita SPA.
- **Deja de escribir directamente en la base de datos con Eloquent para las entidades de negocio clínico** (pacientes, evaluaciones, menús, reportes, citas): pasa a consumir la API REST como cualquier otro cliente, autenticándose con JWT (vía Laravel Socialite/HTTP client, no con `Auth::login` de sesión propia).
- Conserva Eloquent únicamente para lo que le es propio como framework de backoffice: gestión de sesión de UI, cache de vistas, colas internas de notificación por correo (`app/Mail`), subida de logo/configuración visual (`AdminConfiguracionController`).
- Justificación de mantenerlo (no reescribir): ya está construido, probado y en uso por dos roles internos de bajo volumen; el costo de una reescritura no se justifica frente al de integrarlo como cliente API (ver ADR en `14_DecisionesArquitectura.md`).

### 3.3 Web Pública (Padres) — Flask hoy, migración planeada a Next.js
- Responsable de la experiencia pública: landing, calculadora, foro, contacto, información de nutriólogos, login/registro de padres.
- **Fase 1 (corto plazo)**: se mantiene Flask, pero se elimina cualquier acceso directo a Postgres desde Flask (hoy `SQLALCHEMY_DATABASE_URI` apunta directo a la BD "para verificación/futuros modelos" según su propio `.env.flask`) — pasa a consumir exclusivamente la API REST.
- **Fase 2 (mediano plazo, ver `16_PlanModernizacion.md`)**: migración a **Next.js (React + TypeScript)**. Justificación: comparte lenguaje y tipos con la futura app React Native, mejor SEO/SSR para contenido público indexable, ecosistema de componentes reutilizable con el admin si este también evoluciona a React, y facilita compartir un *design system* con la app móvil (paleta, tono, componentes de marca "NutriKids").

### 3.4 App Móvil (React Native, nueva)
- Producto **independiente y distinto de la web**: experiencia gamificada para niños (ver `07_AppMovil.md` completo).
- Consume exclusivamente `/api/v1/*` con JWT emitido para el niño (cuenta vinculada, no necesariamente credenciales propias — ver modelo de cuentas en `03_BaseDatos.md`).
- Responsable de: UI de retos/logros/avatar, registro de hábitos, notificaciones push (vía FCM/APNs), modo offline básico con sincronización diferida.

### 3.5 Base de datos (PostgreSQL)
- Única base de datos transaccional, propiedad exclusiva de la API (FastAPI es dueño del esquema y de las migraciones — ver ADR-003 en `14_DecisionesArquitectura.md`, que revierte la situación actual donde Laravel es dueño del DDL).
- Réplica de solo lectura para reportes/analítica cuando el volumen lo justifique (fase de escala, no día 1).

### 3.6 Cache / Rate limiting / Sesiones efímeras (Redis)
- Sustituye el rate limiter en memoria actual de `fastapi/deps.py` (hallazgo #4 de la auditoría) por un contador distribuido en Redis, válido con N réplicas de la API.
- Cache de lecturas frecuentes (catálogo de alimentos, contenido educativo, tabla de logros/retos).
- Lista de revocación (denylist) de JWT para logout inmediato y para refresh token rotation.

### 3.7 API Gateway / Balanceador (Nginx o Traefik)
- Terminación TLS, enrutamiento a los tres backends (Laravel, API, y en fase 2 Next.js), balanceo entre réplicas de FastAPI, rate limiting de capa 7 como primera línea de defensa (antes de llegar a la app).

### 3.8 Observabilidad (transversal)
- Métricas (Prometheus + Grafana), logs centralizados (Loki o equivalente gestionado), trazado/errores de aplicación (Sentry). Detallado en `05_Seguridad.md` y `09_Cloud.md`.

---

## 4. Flujo de información — ejemplo end-to-end

**Caso: un padre agenda una cita desde la app móvil.**

1. App móvil envía `POST /api/v1/citas` con JWT del padre en `Authorization: Bearer`.
2. Nginx enruta a una réplica de FastAPI (balanceo round-robin).
3. FastAPI valida JWT contra secreto compartido (sin ir a la BD para eso), valida payload con Pydantic, valida regla de negocio (fecha futura, rol `padre`).
4. FastAPI escribe en PostgreSQL (tabla `citas`), invalida cache Redis relacionada (si aplica) y devuelve `201`.
5. Cuando un nutriólogo la toma desde el panel Laravel, Laravel llama `POST /api/v1/citas/{id}/tomar` a la misma API (no escribe Eloquent directo).
6. FastAPI actualiza el estado, y (fase de mensajería futura) publica un evento a una cola para disparar una notificación push al padre.

**Regla derivada**: ninguna escritura de negocio ocurre nunca en dos lugares. Un único camino de escritura por entidad = una única fuente de verdad de validación.

---

## 5. Comunicación entre servicios

| Origen | Destino | Protocolo | Autenticación |
|---|---|---|---|
| Web Admin (Laravel) | API (FastAPI) | HTTP interno (red Docker/VPC) | JWT de servicio o JWT del usuario admin logueado, reenviado |
| Web Pública (Flask/Next.js) | API (FastAPI) | HTTP interno | JWT del usuario padre, o anónimo para lecturas públicas |
| App Móvil | API (FastAPI) | HTTPS público (vía gateway) | JWT (access + refresh) |
| API | PostgreSQL | TCP interno, red privada | credenciales de servicio, nunca expuestas al cliente |
| API | Redis | TCP interno, red privada | password + red privada |
| Todos | Observabilidad | scrape/push interno | sin exposición pública |

**Regla de red**: solo el gateway (Nginx/Traefik) y los backends web (Laravel, Flask/Next.js — porque sirven HTML/assets) están expuestos a Internet. PostgreSQL, Redis y la API en sí (fase de madurez) solo son alcanzables dentro de la red privada; el gateway es el único punto de entrada público.

---

## 6. Ventajas de esta arquitectura

1. **Consistencia**: una sola implementación de reglas de negocio elimina la clase de bug encontrada en la auditoría (dos verificadores de contraseña divergentes).
2. **Escalabilidad independiente**: se pueden añadir réplicas de FastAPI sin tocar Laravel ni el frontend público, porque la API es stateless.
3. **Preparada para móvil desde el diseño**: al no depender de cookies de sesión PHP para el core de negocio, la app React Native consume la misma API que la web sin adaptadores especiales.
4. **Migración incremental de riesgo bajo**: Laravel y Flask no se reescriben de golpe, se convierten en clientes de la API paso a paso (ver `16_PlanModernizacion.md`), cada paso deja el sistema funcional.
5. **Seguridad centralizada**: un único punto donde auditar, loguear y limitar acceso a datos sensibles (expedientes clínicos infantiles), en vez de tres.
6. **Observabilidad simplificada**: los logs y métricas de negocio se concentran en un servicio, no en tres stacks distintos.

## 7. Riesgos de esta arquitectura y cómo se mitigan

| Riesgo | Impacto | Mitigación |
|---|---|---|
| La API se vuelve punto único de fallo | Alto — caída de API tumba los 3 clientes | Múltiples réplicas + health checks + balanceador (`09_Cloud.md`) |
| Latencia añadida: Laravel/Flask ahora llaman HTTP a la API en vez de leer la BD local | Medio | Red interna de baja latencia (mismo VPC/Docker network), cache Redis para lecturas frecuentes |
| Migrar el "dueño" del esquema de Laravel a FastAPI (Alembic) es un cambio delicado | Alto si se hace mal | Ventana de migración controlada, plan detallado en `16_PlanModernizacion.md`, sin downtime mediante migraciones aditivas primero |
| Equipo mixto (PHP + Python + futuro TS/RN) exige más disciplina de contrato de API | Medio | Contrato OpenAPI versionado y documentado (`04_API.md`) como frontera dura entre equipos/agentes |
| Sobre-ingeniería prematura (microservicios, colas, gateway) para el volumen actual (proyecto universitario/early stage) | Medio | Roadmap por fases: gateway y Redis desde fase 1 (bajo costo, alto valor), colas de mensajería y réplicas de lectura solo cuando el volumen lo exija (`12_Roadmap.md`) |

---

## 8. Qué NO cambia en el corto plazo

- No se reescribe Laravel como SPA de inmediato — sigue siendo Blade + sesión de UI para el backoffice.
- No se introduce Kubernetes ni microservicios adicionales mientras Docker Compose + 1-2 réplicas resuelvan la carga real (ver criterios de escalado en `09_Cloud.md`).
- No se cambia PostgreSQL por otro motor (justificación en `14_DecisionesArquitectura.md`).

Este documento define el **destino**. El camino paso a paso, sin romper lo que funciona hoy, está en `16_PlanModernizacion.md` y `12_Roadmap.md`.
