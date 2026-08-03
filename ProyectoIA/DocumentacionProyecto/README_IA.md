# README_IA.md — Punto de Entrada para Agentes de IA

> Si eres un agente de IA (Claude Code, Cursor, Antigravity, ChatGPT u otro) empezando a trabajar en NutriKids, **lee este archivo primero, completo, antes de tocar cualquier código o documento**.

---

## 1. Qué es esta carpeta

`ProyectoIA/DocumentacionProyecto/` es la **fuente única de verdad** técnica y de producto del proyecto NutriKids. Fue generada por el Arquitecto Principal del proyecto tras una auditoría completa y verificada del código real (no de suposiciones). Todo lo que necesitas para entender el proyecto y trabajar en él de forma consistente está aquí — **no vuelvas a auditar el repositorio completo desde cero**; si algo parece faltar, primero revisa si ya está cubierto antes de investigar por tu cuenta.

## 2. Orden de lectura recomendado

1. **`00_ResumenGeneral.md`** — visión de 5 minutos.
2. **`EstadoProyecto.md`** — en qué punto exacto quedó el proyecto ahora mismo (léelo siempre, es el documento que más cambia).
3. **`01_AuditoriaProyecto.md`** — qué existe realmente en el código hoy, verificado.
4. **`02_Arquitectura.md`** — hacia dónde va el proyecto y por qué.
5. A partir de aquí, entra al documento específico de tu tarea: `03_BaseDatos.md`, `04_API.md`, `05_Seguridad.md`, `06_Web.md`, `07_AppMovil.md`, `08_Docker.md`, `09_Cloud.md`, `10_Pruebas.md`, `11_Deployment.md`.
6. **`12_Roadmap.md`** y **`13_Backlog.md`** — para saber qué tarea concreta te corresponde y en qué fase estamos.
7. **`14_DecisionesArquitectura.md`** — antes de tomar cualquier decisión de diseño, revisa si ya fue decidida (y por qué) aquí.
8. **`FlujoTrabajoIA.md`** — confirma que la tarea que vas a hacer corresponde a tu rol de agente.
9. **`PROMPTS.md`** — si necesitas un prompt ya calibrado para tu tarea, probablemente ya existe ahí.

## 3. Reglas no negociables para cualquier agente

1. **No re-auditar el proyecto completo desde cero.** `01_AuditoriaProyecto.md` ya es la auditoría verificada. Si el código cambió de forma que la contradice, actualiza ese documento puntualmente — no repitas el trabajo completo.
2. **No implementar nada que contradiga una decisión ya tomada** en `14_DecisionesArquitectura.md` sin proponer una nueva ADR que la reemplace explícitamente.
3. **No saltarte el orden de migración** de `16_PlanModernizacion.md` — varios pasos son secuenciales por diseño, ejecutarlos fuera de orden arriesga pérdida de datos clínicos de menores.
4. **No tomar decisiones de producto de la app móvil que contradigan `07_AppMovil.md` §8** (sin leaderboard entre familias, sin exponer datos corporales crudos al niño, sin publicidad) sin que el humano responsable del proyecto lo apruebe explícitamente — son decisiones de protección de menores, no de preferencia técnica.
5. **Todo cambio de código relevante se refleja en la documentación en el mismo cambio**, no después. Documentación desactualizada es peor que no tener documentación, porque genera confianza falsa.
6. **Al terminar cualquier tarea**: actualiza `Bitacora.md` (qué hiciste y cuándo) y, si corresponde, `EstadoProyecto.md` y `13_Backlog.md` (marca la tarea como completada).

## 4. Si algo en el código contradice esta documentación

Prioriza el código real como hecho, pero **no lo asumas silenciosamente** — actualiza el documento afectado en el mismo cambio y dejá constancia en `Bitacora.md` de qué se corrigió y por qué. La documentación debe seguir siendo confiable para el siguiente agente.

## 5. Mapa completo de documentos

Ver la tabla completa en `00_ResumenGeneral.md` §4 — no se repite aquí para evitar que ambos documentos diverjan con el tiempo.

## 6. Contacto de decisión humana

Este ecosistema de documentación asume que hay un responsable humano del proyecto que aprueba: despliegues a producción, decisiones de arquitectura mayores (nuevas ADRs que reemplacen una existente), y cualquier cambio que afecte el tratamiento de datos de menores. Ningún agente de IA está autorizado a tomar esas decisiones de forma unilateral, por capaz que sea — ver `FlujoTrabajoIA.md` para el detalle de qué corresponde a cada agente.
