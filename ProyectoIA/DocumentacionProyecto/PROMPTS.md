# PROMPTS.md — Prompts Reutilizables por Fase

> Cada prompt está pensado para pegarse tal cual (con los `[placeholders]` completados) en Claude Code, Cursor o Antigravity. Todos comparten una regla fija: **leer primero la documentación de `ProyectoIA/DocumentacionProyecto/` referenciada antes de escribir código**. Ningún prompt autoriza a re-auditar el proyecto completo desde cero — la auditoría ya existe en `01_AuditoriaProyecto.md`.

---

## Prompt — Base de Datos (Alembic / esquema)

```
Rol: ingeniero de backend Python especializado en SQLAlchemy/Alembic.

Contexto obligatorio a leer antes de escribir código:
- ProyectoIA/DocumentacionProyecto/01_AuditoriaProyecto.md (estado real actual)
- ProyectoIA/DocumentacionProyecto/03_BaseDatos.md (diseño objetivo)
- ProyectoIA/DocumentacionProyecto/14_DecisionesArquitectura.md (ADR-003, ADR-006)
- ProyectoIA/DocumentacionProyecto/16_PlanModernizacion.md (Paso 4 y 5 — orden obligatorio de migración)

Tarea: [ej. "Implementar la migración de Alembic que crea la entidad `ninos` en paralelo a `pacientes`, según 03_BaseDatos.md §3.4, siguiendo el Paso 5.1 de 16_PlanModernizacion.md — NO eliminar `pacientes` todavía."]

Reglas no negociables:
- No modificar tablas fuera del alcance de la tarea.
- Toda migración debe ser reversible (`downgrade`) salvo que sea explícitamente un borrado de datos, en cuyo caso se documenta en el PR.
- No usar `DROP TABLE`/`DROP COLUMN` en el mismo paso que se deja de usar esa columna — separar en dos migraciones (11_Deployment.md §4).
- Añadir índices según lo especificado en 03_BaseDatos.md, no solo la FK.
- Al terminar, actualizar 14_DecisionesArquitectura.md si tomaste una decisión de diseño no cubierta ya por un ADR existente, y registrar el paso en Bitacora.md.

Entregable: migración de Alembic + modelo SQLAlchemy actualizado + tests de integración mínimos (10_Pruebas.md §2.1) que verifiquen que la tabla nueva tiene las restricciones e índices esperados.
```

---

## Prompt — Seguridad (auth, rate limiting, RBAC)

```
Rol: ingeniero de seguridad de aplicaciones backend.

Contexto obligatorio a leer antes de escribir código:
- ProyectoIA/DocumentacionProyecto/01_AuditoriaProyecto.md §6 (gaps reales verificados)
- ProyectoIA/DocumentacionProyecto/05_Seguridad.md (estrategia completa)
- ProyectoIA/DocumentacionProyecto/03_BaseDatos.md §3.3 (refresh_tokens)
- ProyectoIA/DocumentacionProyecto/14_DecisionesArquitectura.md (ADR-005, ADR-007)

Tarea: [ej. "Implementar refresh tokens con rotación y detección de reuso según 05_Seguridad.md §1.2 (tarea T1.3 de 13_Backlog.md)."]

Reglas no negociables:
- Nunca loguear contraseñas, PINs, ni tokens completos (05_Seguridad.md §8).
- Todo secreto nuevo se lee de variable de entorno, nunca hardcodeado (05_Seguridad.md §5).
- Toda respuesta de error de auth debe seguir el formato uniforme de 04_API.md §3.2, sin filtrar detalles internos.
- Los tests deben cubrir explícitamente el caso adversarial (token expirado, token reutilizado, rol incorrecto, acceso cross-tenant), no solo el happy path.

Entregable: implementación + tests de seguridad correspondientes (10_Pruebas.md §2.1 "Seguridad") + actualización de 05_Seguridad.md §10 (checklist) si corresponde.
```

---

## Prompt — API (nuevo endpoint o recurso)

```
Rol: ingeniero backend FastAPI.

Contexto obligatorio a leer antes de escribir código:
- ProyectoIA/DocumentacionProyecto/04_API.md (convenciones de contrato)
- ProyectoIA/DocumentacionProyecto/03_BaseDatos.md (entidad/es involucradas)
- ProyectoIA/DocumentacionProyecto/05_Seguridad.md §2 (RBAC por fila)

Tarea: [ej. "Implementar los endpoints de /habitos-catalogo y /ninos/{id}/habitos según 04_API.md, tarea T3.1 de 13_Backlog.md."]

Reglas no negociables:
- Prefijo /api/v1/, JSON siempre, paginación en toda colección (04_API.md §3.1).
- RBAC por rol Y por fila (un padre solo ve sus propios ninos) — nunca confiar en un filtro enviado por el cliente.
- Formato de error uniforme (04_API.md §3.2).
- El endpoint debe quedar reflejado correctamente en /docs (Swagger) sin romper el openapi.json de forma incompatible con clientes existentes (11_Deployment.md §2.3) — si el cambio es incompatible, debe documentarse como tal en 14_DecisionesArquitectura.md.

Entregable: endpoint + esquema Pydantic + tests de integración (happy path + RBAC denegado, 10_Pruebas.md §2.1).
```

---

## Prompt — React Native / App Móvil

```
Rol: ingeniero frontend React Native, especializado en apps para público infantil.

Contexto obligatorio a leer antes de escribir código:
- ProyectoIA/DocumentacionProyecto/07_AppMovil.md (diseño completo: pantallas, gamificación, decisiones de producto)
- ProyectoIA/DocumentacionProyecto/04_API.md (contrato de API a consumir)
- ProyectoIA/DocumentacionProyecto/05_Seguridad.md §7 (datos de menores)

Tarea: [ej. "Implementar la pantalla de Login con PIN y selección de perfil según 07_AppMovil.md §3.1 y §3.2, tarea T4.3 de 13_Backlog.md."]

Reglas no negociables:
- Respetar las decisiones de producto de 07_AppMovil.md §8 (sin leaderboard entre familias, sin mostrar peso/IMC al niño, sin publicidad).
- Tokens JWT se guardan en Keychain/Keystore (react-native-keychain o equivalente), nunca en AsyncStorage plano.
- Tono visual positivo, sin comparación negativa, iconografía grande y clara para el rango de edad objetivo.
- No implementar ninguna pantalla o feature no descrita en el mapa de navegación de 07_AppMovil.md §3 sin antes añadirla ahí y justificarla.

Entregable: pantalla(s) + integración con el cliente API tipado (04_API.md §5) + al menos un test de componente.
```

---

## Prompt — Docker / Infraestructura

```
Rol: ingeniero DevOps.

Contexto obligatorio a leer antes de escribir código:
- ProyectoIA/DocumentacionProyecto/08_Docker.md (infraestructura objetivo)
- ProyectoIA/DocumentacionProyecto/01_AuditoriaProyecto.md (docker-compose.yml actual, verificado)
- ProyectoIA/DocumentacionProyecto/05_Seguridad.md §3 (red y transporte)

Tarea: [ej. "Añadir el contenedor Redis al docker-compose.yml según 08_Docker.md, tarea T1.1 de 13_Backlog.md."]

Reglas no negociables:
- Ningún puerto de base de datos/cache se publica al host salvo justificación explícita de desarrollo local documentada.
- Todo servicio nuevo lleva healthcheck.
- Ningún secreto se hardcodea en docker-compose.yml.
- No romper el hot-reload de desarrollo local existente para laravel/fastapi/flask salvo que la tarea sea explícitamente sobre el build de producción (11_Deployment.md §4).

Entregable: docker-compose.yml actualizado + verificación de que `docker-compose up` levanta el stack completo sin errores.
```

---

## Prompt — Despliegue / CI-CD

```
Rol: ingeniero de plataforma (CI/CD).

Contexto obligatorio a leer antes de escribir código:
- ProyectoIA/DocumentacionProyecto/11_Deployment.md
- ProyectoIA/DocumentacionProyecto/10_Pruebas.md
- ProyectoIA/DocumentacionProyecto/09_Cloud.md

Tarea: [ej. "Configurar el pipeline de CI que ejecuta lint + tests segmentados por carpeta afectada según 11_Deployment.md §2."]

Reglas no negociables:
- El pipeline nunca despliega a producción sin pasar por staging y smoke test (11_Deployment.md §3).
- Ningún secreto de CI en el repositorio.
- Un PR que solo toca una capa (p. ej. app móvil) no debe disparar la suite completa de otra capa (Laravel) sin necesidad.

Entregable: configuración de pipeline + documentación de qué dispara qué (actualizar 11_Deployment.md si el diseño real difiere del documentado).
```

---

## Prompt — Testing

```
Rol: ingeniero de QA automatizado.

Contexto obligatorio a leer antes de escribir código:
- ProyectoIA/DocumentacionProyecto/10_Pruebas.md
- El documento de la capa específica bajo prueba (04_API.md, 07_AppMovil.md, 06_Web.md según corresponda)

Tarea: [ej. "Escribir tests de integración para los endpoints de /citas cubriendo RBAC por rol y por fila, según 10_Pruebas.md §2.1."]

Reglas no negociables:
- Priorizar cobertura de reglas de negocio y de seguridad sobre cobertura de línea bruta (10_Pruebas.md §4).
- Todo test de RBAC debe incluir explícitamente el caso denegado, no solo el permitido.
- No usar datos reales de menores en fixtures — solo datos ficticios (10_Pruebas.md §3).

Entregable: suite de tests + reporte de qué queda sin cubrir (si algo queda fuera de alcance, decirlo explícitamente, no omitirlo en silencio).
```

---

## Regla transversal para todos los prompts anteriores

Cualquier agente que ejecute uno de estos prompts debe, al finalizar:
1. Actualizar `Bitacora.md` con lo que se hizo y la fecha.
2. Marcar la tarea correspondiente como completada en `13_Backlog.md` (o anotar por qué quedó parcial).
3. Si tomó una decisión de diseño no cubierta por un ADR existente, añadir una entrada en `14_DecisionesArquitectura.md`.
4. Si el cambio afecta el estado descrito en `EstadoProyecto.md`, actualizarlo.

Ver `FlujoTrabajoIA.md` para qué agente debe usarse para cada tipo de prompt y qué no debe hacer cada uno.
