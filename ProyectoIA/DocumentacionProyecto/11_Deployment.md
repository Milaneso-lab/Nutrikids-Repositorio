# 11 — Estrategia de Despliegue (CI/CD)

> Depende de: [`08_Docker.md`](./08_Docker.md), [`09_Cloud.md`](./09_Cloud.md), [`10_Pruebas.md`](./10_Pruebas.md).

---

## 1. Entornos

| Entorno | Propósito | Despliegue |
|---|---|---|
| Local | Desarrollo de cada agente/dev | `docker-compose.yml` con hot-reload |
| Staging | Validación previa a producción, datos ficticios | Automático en cada merge a `main`/`develop` (según estrategia de ramas del equipo) |
| Producción | Usuarios reales | Manual (aprobación explícita) o automático solo tras tag de release, según madurez del equipo |

## 2. Pipeline de CI (por cada Pull Request)

1. **Lint**: `laravel/pint` (ya en `composer.json`) para PHP; `ruff`/`black` (a incorporar) para Python; `eslint` para TS/RN cuando exista ese código.
2. **Tests** (según `10_Pruebas.md`): unitarias + integración de la capa afectada por el PR. Un PR que solo toca la app móvil no re-ejecuta la suite completa de Laravel (pipeline segmentado por carpeta afectada, para tiempos de CI razonables).
3. **Contrato de API**: si el PR toca `fastapi/`, se valida que el `openapi.json` resultante no elimina ni cambia de tipo un campo existente sin una entrada correspondiente en `14_DecisionesArquitectura.md` (rotura de contrato intencional y documentada, no accidental).
4. **Build de imágenes Docker**: build de cada servicio afectado, sin publicar aún.
5. **Escaneo de seguridad básico**: dependencias vulnerables (`composer audit`, `pip-audit`, `npm audit`) — bloqueante solo para severidad alta/crítica.

## 3. Pipeline de CD (al hacer merge / tag)

1. Build y push de imágenes versionadas (tag = hash de commit o versión semántica) a un registro de contenedores.
2. Despliegue automático a **staging**.
3. Smoke test post-despliegue (`GET /health` de `api`, `laravel`, `web` — los tres ya exponen o pueden exponer un endpoint de salud).
4. Promoción a **producción**: manual en fases tempranas del proyecto (control humano explícito dado que se maneja datos de salud infantil), automatizable más adelante cuando la suite de pruebas dé suficiente confianza histórica.

## 4. Estrategia de despliegue sin downtime

- **Imágenes inmutables** en producción (se elimina el bind-mount de código usado en desarrollo, según `08_Docker.md` §3).
- **Rolling deploy** de las réplicas de `api`: se despliega una réplica nueva, pasa su healthcheck, recibe tráfico, se retira la anterior — nunca todas las réplicas caen a la vez.
- **Migraciones de base de datos aditivas primero**: nunca se elimina una columna/tabla en el mismo despliegue que deja de usarla en código; se separa en dos despliegues (dejar de usar → luego eliminar), evitando romper una réplica antigua que aún corre durante el rolling deploy.

## 5. Rollback

- Cada despliegue queda asociado a una imagen versionada anterior conocida-buena; rollback = re-desplegar la versión anterior (no un `git revert` bajo presión).
- Migraciones de base de datos se diseñan reversibles cuando es razonable (`down()` de Alembic/Laravel); cuando no lo son (p. ej. borrado de datos), se documenta explícitamente en el PR que introduce la migración.

## 6. Gestión de secretos en el pipeline

- Secretos de CI/CD (credenciales de registro de imágenes, claves de despliegue) en el almacén de secretos del proveedor de CI (GitHub Actions Secrets o equivalente), nunca en el repositorio — mismo principio de `05_Seguridad.md` §5 aplicado al pipeline mismo.

## 7. Qué agente hace qué en este flujo

Ver `FlujoTrabajoIA.md` para la división de responsabilidades entre Claude Code, Cursor y Antigravity dentro de este pipeline (quién escribe el workflow de CI, quién lo ejecuta, quién no debe tocar producción).
