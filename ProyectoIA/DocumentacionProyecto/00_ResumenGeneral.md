# 00 — Resumen General del Proyecto NutriKids

> **Propósito de este documento**: dar a cualquier persona o agente de IA que entre por primera vez al proyecto una visión completa en menos de 5 minutos, con enlaces al resto de la documentación para profundizar. Es el "elevator pitch" técnico, no el detalle.

---

## 1. Qué es NutriKids

NutriKids es una plataforma dirigida a **combatir la obesidad infantil** conectando tres actores:

- **Padres/tutores**: gestionan el perfil de sus hijos, agendan citas con nutriólogos, consultan contenido educativo y (a partir del roadmap) supervisan el progreso de sus hijos en la app móvil.
- **Nutriólogos**: gestionan pacientes, evaluaciones antropométricas, planes de menú, reportes clínicos y citas.
- **Administradores**: gestionan usuarios, contenido del sitio público, moderación de foro/comentarios y configuración general.
- **Niños** (nuevo actor, vía app móvil): interactúan con una experiencia gamificada de hábitos saludables, sincronizada con la cuenta de sus padres.

## 2. Estado actual (as-is) — resumen

Hoy existe una **plataforma web funcional** compuesta por tres aplicaciones independientes que comparten una base de datos PostgreSQL:

- **Laravel** (PHP, puerto 8080): panel de administración y de nutriólogo, autenticación por sesión.
- **FastAPI** (Python, puerto 8000): API REST interna que expone `/api/*` con JWT y RBAC.
- **Flask** (Python, puerto 5000): portal público de cara a los padres.

El detalle exhaustivo, verificado línea por línea contra el código, está en **[`01_AuditoriaProyecto.md`](./01_AuditoriaProyecto.md)**. Los hallazgos más relevantes: hay lógica de autenticación duplicada entre Laravel y FastAPI, y varias tablas del dominio clínico (`infantes`, `alertas`, `alergias`, `notas_nutriologo`, `menus_semanales`) están creadas pero sin columnas reales — son placeholders sin implementar.

## 3. Hacia dónde va el proyecto (to-be)

El proyecto evolucionará hacia un **ecosistema completo**:

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  Web Admin/   │     │  Web Pública │     │  App Móvil    │
│  Nutriólogo   │     │  (Padres)    │     │  (Niños, RN) │
└───────┬──────┘     └───────┬──────┘     └───────┬──────┘
        │                    │                    │
        └────────────────────┼────────────────────┘
                              ▼
                    ┌───────────────────┐
                    │   API REST única   │  ← fuente única de verdad
                    │     (FastAPI)      │     de negocio y datos
                    └─────────┬─────────┘
                              ▼
                    ┌───────────────────┐
                    │  PostgreSQL + Redis │
                    └───────────────────┘
```

incorporando además: infraestructura Docker robusta, seguridad de nivel empresarial (JWT + refresh, RBAC granular, rate limiting distribuido, secretos gestionados), despliegue en la nube con balanceo de carga, monitoreo (métricas + logs + trazas) y una **app móvil en React Native orientada a niños** (gamificación, retos, recompensas, hábitos, avatar) que es un producto **distinto** de la web, no una réplica.

El diseño completo de esta arquitectura objetivo está en **[`02_Arquitectura.md`](./02_Arquitectura.md)**.

## 4. Cómo está organizada esta documentación

Toda la documentación vive en `ProyectoIA/DocumentacionProyecto/` y es la **fuente única de verdad** para cualquier agente de IA (Claude Code, Cursor, Antigravity, ChatGPT) que trabaje en el proyecto a partir de ahora. El orden de lectura recomendado está en **[`README_IA.md`](./README_IA.md)**.

| Documento | Contenido |
|---|---|
| `00_ResumenGeneral.md` | Este documento. |
| `01_AuditoriaProyecto.md` | Estado real del repo, verificado contra el código. |
| `02_Arquitectura.md` | Arquitectura objetivo, responsabilidades, flujos, riesgos. |
| `03_BaseDatos.md` | Diseño de datos objetivo (entidades, relaciones, índices). |
| `04_API.md` | Contrato de la API REST central. |
| `05_Seguridad.md` | Estrategia de seguridad empresarial. |
| `06_Web.md` | Diseño objetivo de la plataforma web. |
| `07_AppMovil.md` | Diseño completo de la app móvil (React Native). |
| `08_Docker.md` | Infraestructura de contenedores objetivo. |
| `09_Cloud.md` | Despliegue en la nube, balanceo, escalabilidad. |
| `10_Pruebas.md` | Estrategia de testing por capa. |
| `11_Deployment.md` | CI/CD, entornos, rollback. |
| `12_Roadmap.md` | Fases de desarrollo, cada una con punto estable. |
| `13_Backlog.md` | Tareas accionables con criterios de aceptación. |
| `14_DecisionesArquitectura.md` | ADRs — por qué se decidió cada cosa. |
| `15_Riesgos.md` | Matriz de riesgos y mitigaciones. |
| `16_PlanModernizacion.md` | Cómo migrar del estado actual al objetivo sin romper nada. |
| `PROMPTS.md` | Prompts reutilizables por fase para agentes de IA. |
| `FlujoTrabajoIA.md` | Qué hace y qué NO hace cada agente de IA. |
| `EstadoProyecto.md` | Estado vivo — se actualiza en cada sesión de trabajo. |
| `Bitacora.md` | Historial cronológico de decisiones. |
| `README_IA.md` | Punto de entrada y reglas para agentes de IA. |

## 5. Principios rectores del proyecto (no negociables)

1. **Una sola fuente de verdad de datos y de negocio**: la API REST (FastAPI). Ningún cliente (web admin, web pública, app móvil) accede directamente a la base de datos ni duplica lógica de negocio.
2. **La app móvil no es un espejo de la web**: es un producto de gamificación para niños con su propia lógica de UX, sincronizado con las cuentas de los padres vía API.
3. **Seguridad por diseño, no por parche**: todo endpoint nuevo se diseña con su modelo de amenaza antes de implementarse (ver `05_Seguridad.md`).
4. **Cada fase del roadmap termina en un estado desplegable y estable** — nunca se deja el proyecto en un estado roto entre fases (ver `12_Roadmap.md`).
5. **Todo documento de esta carpeta se mantiene vivo**: si el código cambia de forma que contradice un documento, el documento se actualiza en el mismo cambio, no después.

## 6. Alcance de este documento

Este resumen **no sustituye** la lectura de `01_AuditoriaProyecto.md` ni de `02_Arquitectura.md` antes de tomar decisiones de implementación. Es solo el mapa; el territorio está en los documentos referenciados.
