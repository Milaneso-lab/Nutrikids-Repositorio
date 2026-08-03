# FlujoTrabajoIA.md — Responsabilidades de cada Agente de IA

> Objetivo: que Claude Code, Cursor, Antigravity y ChatGPT puedan trabajar sobre el mismo proyecto sin pisarse ni duplicar trabajo, cada uno en lo que mejor resuelve. Ningún agente debe asumir un rol fuera de lo aquí descrito sin que el humano responsable del proyecto lo autorice explícitamente.

---

## 1. Claude Code

**Rol principal**: implementación de backend, cambios multi-archivo, refactor estructural, migraciones de base de datos, lógica de seguridad, integración entre servicios (Laravel↔API, App↔API).

**Debe hacer**:
- Cambios de codebase que tocan varios archivos o varias capas (p. ej. mover un controlador de Eloquent directo a cliente HTTP de la API — Paso 6 de `16_PlanModernizacion.md`).
- Migraciones de Alembic/base de datos, siguiendo estrictamente el orden de `16_PlanModernizacion.md`.
- Implementación de reglas de seguridad (`05_Seguridad.md`): JWT, refresh tokens, RBAC, rate limiting.
- Escribir y mantener pruebas automatizadas (`10_Pruebas.md`).
- Actualizar la documentación de `ProyectoIA/DocumentacionProyecto/` cuando el código que produce diverge de lo documentado, o cuando toma una decisión de arquitectura no cubierta por un ADR existente.

**No debe hacer**:
- Desplegar a producción sin aprobación humana explícita (`11_Deployment.md` §3).
- Tomar decisiones de diseño visual/UX de la app móvil (eso es de producto/diseño, ver Antigravity).
- Re-auditar el proyecto completo desde cero — debe partir de `01_AuditoriaProyecto.md` y solo ampliar esa auditoría si detecta información faltante o desactualizada.
- Ejecutar migraciones destructivas (`DROP TABLE`/`DROP COLUMN`) sin haber separado el cambio en dos despliegues según `11_Deployment.md` §4.

---

## 2. Cursor

**Rol principal**: edición asistida rápida dentro del IDE, cambios acotados a un archivo o un módulo pequeño, iteración rápida sobre UI ya scaffoldeada.

**Debe hacer**:
- Cambios puntuales de UI en el panel Laravel/Blade (p. ej. CRUD de catálogos de gamificación, tarea T3.2 de `13_Backlog.md`).
- Correcciones de estilo, lint, ajustes menores de copy en pantallas ya existentes.
- Limpieza de archivos residuales acotada (p. ej. tareas T0.3-T0.5 de `13_Backlog.md`).

**No debe hacer**:
- Decisiones de arquitectura o cambios de esquema de base de datos — eso pasa por Claude Code y queda registrado en `14_DecisionesArquitectura.md`.
- Cambios que toquen el contrato de la API sin antes verificar `04_API.md` — un cambio de contrato hecho "rápido" desde el IDE sin ese contexto es la fuente de bugs de integración más probable.

---

## 3. Antigravity

**Rol principal**: scaffolding de proyectos nuevos y generación de interfaces, especialmente para la app móvil React Native (dominio nuevo, sin código heredado que respetar).

**Debe hacer**:
- Inicialización de la estructura del proyecto React Native (`07_AppMovil.md` §10, tarea T4.1).
- Generación de pantallas y componentes de UI siguiendo el mapa de navegación y el tono de diseño de `07_AppMovil.md` (§3, §1).
- Prototipado visual del design system de la app (colores, tipografía, iconografía de gamificación) alineado con la identidad de marca NutriKids.

**No debe hacer**:
- Implementar lógica de negocio de gamificación (cálculo de puntos, condición de retos) — esa lógica vive en la API (Claude Code), la app solo la consume y la muestra.
- Decidir el modelo de datos — debe consumir el contrato ya definido en `04_API.md`/`03_BaseDatos.md`, no inventar campos nuevos sin pasar por esos documentos.
- Introducir dependencias/paquetes de terceros sin verificar que no dupliquen algo ya decidido en `14_DecisionesArquitectura.md`.

---

## 4. ChatGPT

**Rol principal**: research puntual, redacción de contenido no técnico (copy educativo del sitio público, textos de ayuda, nombres/descripciones de logros y retos), revisión de claridad de documentación existente.

**Debe hacer**:
- Redactar o revisar contenido educativo sobre nutrición infantil para el sitio público (`06_Web.md`) — no genera código de producción, solo texto.
- Proponer nombres, descripciones y tono de copy para el catálogo de hábitos/retos/logros/recompensas (`03_BaseDatos.md` §6), a validar por el equipo antes de cargarse en el catálogo real.
- Servir como segunda opinión de research (p. ej. comparar proveedores cloud alternativos) cuyo resultado se documenta como propuesta en `14_DecisionesArquitectura.md`, no se ejecuta directamente.

**No debe hacer**:
- Escribir o modificar código de producción del repositorio.
- Tomar decisiones de arquitectura de forma unilateral — sus propuestas de research alimentan una decisión que se registra formalmente en `14_DecisionesArquitectura.md`, revisada por el equipo/agente responsable de implementar.

---

## 5. Regla de coordinación entre agentes

Cuando dos agentes trabajan en la misma fase de `12_Roadmap.md` en paralelo, deben limitarse a los pasos marcados como paralelizables en `16_PlanModernizacion.md` §4. Cualquier trabajo que toque el esquema de base de datos o el contrato de la API es **secuencial y exclusivo de un solo agente a la vez** — el riesgo de una migración de datos corriendo en paralelo con otro cambio de esquema es alto (ver `15_Riesgos.md`).

Todo agente, sin excepción, debe:
1. Leer `EstadoProyecto.md` antes de empezar cualquier tarea, para saber en qué punto quedó el proyecto.
2. Registrar su trabajo en `Bitacora.md` al terminar.
3. No modificar código fuente fuera del alcance de la tarea asignada (principio ya establecido en `PROMPTS.md` — regla transversal).
