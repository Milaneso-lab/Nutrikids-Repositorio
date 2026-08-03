# 16 — Plan de Modernización Incremental

> Traduce `02_Arquitectura.md` (destino) + `01_AuditoriaProyecto.md` (origen) en una secuencia de pasos concretos y de bajo riesgo. Complementa `12_Roadmap.md` (fases de producto) con el detalle específico de *cómo* migrar sin romper lo que funciona hoy.

---

## 1. Principio: *strangler fig*, no reescritura

Ningún componente se apaga hasta que su reemplazo está verificado en paralelo. Se sigue el patrón de "figura estranguladora": el sistema nuevo crece alrededor del viejo, ruta por ruta / entidad por entidad, hasta que el viejo deja de recibir tráfico y se retira.

## 2. Secuencia concreta de modernización

### Paso 1 — Limpieza sin riesgo (Fase 0 de `12_Roadmap.md`)
Cambios que no alteran comportamiento observable: cerrar exposición de Postgres, sacar secretos del compose, unificar `.env.example`, eliminar `web1_flask/`. Cero impacto en usuarios, se ejecutan primero porque reducen riesgo de seguridad inmediato sin esperar al resto del plan.

### Paso 2 — Auth unificada sin cambiar UX (Fase 1)
Laravel deja de verificar contraseñas por su cuenta y llama a `/auth/login` de la API (T1.5 de `13_Backlog.md`). **Verificación de que no rompió nada**: el formulario de login visible para el usuario, sus redirecciones por rol y sus mensajes de error deben comportarse exactamente igual que hoy — se valida con los mismos casos de prueba manuales usados hoy antes de considerar el paso cerrado.

### Paso 3 — Redis y gateway como capas añadidas, no sustituciones
Se añaden `redis` y `gateway` al compose sin quitar nada existente; los puertos actuales (8080/8000/5000) se mantienen accesibles en paralelo durante la transición para no romper flujos de desarrollo en curso, y se retiran solo cuando el gateway esté confirmado como único punto de entrada funcional.

### Paso 4 — Cambio de propiedad del esquema (ADR-003), el paso de mayor riesgo técnico
Orden estricto para evitar pérdida de datos:
1. Se genera la migración baseline de Alembic **a partir del esquema actual tal cual está** (sin cambios de forma todavía) — Alembic aprende a describir lo que ya existe.
2. Se verifica en un entorno de staging que `alembic upgrade head` sobre una copia de los datos reales no produce diffs inesperados.
3. Solo entonces se congelan nuevas migraciones Laravel para tablas de negocio; Laravel sigue leyendo/escribiendo esas tablas *a través de la API* desde este punto (ya cubierto por el Paso 2 en la parte de auth, se generaliza al resto de entidades en el Paso 5).

### Paso 5 — Consolidación de esquema de datos (ADR-006 y tablas cáscara)
Orden dentro de este paso:
1. Crear la tabla `ninos` **en paralelo** a `pacientes` (no se borra `pacientes` todavía).
2. Migrar datos de `pacientes` a `ninos`, marcando explícitamente los registros con `padre_id` desconocido (riesgo documentado en `15_Riesgos.md`) para resolución humana antes de continuar.
3. Cambiar los endpoints de la API para que lean/escriban `ninos` en vez de `pacientes`.
4. Verificar en staging que el panel de nutriólogo/admin sigue mostrando toda la información esperada.
5. Solo entonces, eliminar `pacientes` e `infantes` (tras un periodo de retención de la tabla antigua como backup de seguridad, no un `DROP TABLE` inmediato).
6. Repetir el mismo patrón (crear en paralelo → migrar → verificar → retirar) para materializar `alertas`, `alergias`, `notas_nutriologo`, `menus_semanales` con columnas reales.

### Paso 6 — Migración de escrituras de negocio de Laravel/Flask a la API (completar ADR-002)
Controlador por controlador (no todos a la vez): se migra `PacienteController`, se verifica, se migra `EvaluacionController`, se verifica, y así sucesivamente — cada controlador migrado es un punto de verificación independiente, siguiendo exactamente la lista de tareas T2.4 en `13_Backlog.md`.

### Paso 7 — Gamificación y app móvil
Se construyen como **capas nuevas** sobre el esquema ya consolidado (Fase 3 y 4 de `12_Roadmap.md`) — no requieren "migrar" nada existente, son funcionalidad aditiva pura, el paso de menor riesgo técnico de todo el plan.

### Paso 8 — Migración de la web pública a Next.js (opcional, Fase 6)
Página por página: se implementa la versión Next.js de una ruta pública, se despliega en un subpath o subdominio de prueba, se compara visualmente y funcionalmente contra la versión Flask, y solo entonces el gateway redirige esa ruta específica al nuevo servicio. Flask sigue sirviendo el resto de rutas no migradas hasta que todas lo estén.

## 3. Reglas de seguridad del propio plan de migración

- Ninguna migración de datos se ejecuta directamente en producción sin haberse probado antes en staging con una copia de datos reales (anonimizada si staging no tiene el mismo nivel de control de acceso que producción, dado que se trata de datos de menores).
- Toda migración de esquema que implique borrado de columnas/tablas se separa en dos despliegues (dejar de usar → luego eliminar), consistente con `11_Deployment.md` §4.
- Cada paso de este plan que se ejecute se registra en `Bitacora.md` con fecha y resultado, para que un agente que retome el proyecto meses después sepa exactamente en qué paso se quedó (ver también `EstadoProyecto.md`).

## 4. Qué pasos pueden ejecutarse en paralelo por agentes distintos

| Paso | Paralelizable con | Nota |
|---|---|---|
| Paso 1 (limpieza) | Cualquier otro | Sin dependencias |
| Paso 7 (gamificación backend + app móvil scaffolding) | Pasos 4-6 | El dominio de gamificación no depende del esquema clínico existente, puede avanzar en paralelo por un agente distinto mientras otro ejecuta la migración de esquema |
| Pasos 2-6 | Entre sí, **no** — son secuenciales por diseño (cada uno depende de que el anterior esté verificado) | Ver `FlujoTrabajoIA.md` para cómo coordinar esto entre agentes sin pisarse |
