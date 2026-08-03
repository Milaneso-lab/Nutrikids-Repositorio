# 12 — Roadmap por Fases

> Cada fase termina en un **punto estable y desplegable**: el sistema funciona de principio a fin al cierre de cada fase, nunca queda a medias entre una fase y la siguiente. Depende de todos los documentos 02-09. Las tareas accionables de cada fase están detalladas en `13_Backlog.md`.

---

## Fase 0 — Consolidación del estado actual (estabilización, sin nuevas features)

**Objetivo**: cerrar los hallazgos de la auditoría que representan riesgo real, sin tocar aún arquitectura ni añadir producto nuevo.

- Corregir exposición de PostgreSQL al host en `docker-compose.yml` (hallazgo #5).
- Sacar secretos hardcodeados del compose hacia `.env` (hallazgo #6).
- Unificar y corregir `.env.example` (puerto de Flask inconsistente, hallazgo #8).
- Eliminar o completar `InfanteController.php` (hallazgo #9).
- Eliminar carpeta residual `web1_flask/` (hallazgo #10), previa confirmación de que no se usa en ningún flujo.

**Punto estable de cierre**: la plataforma actual sigue funcionando exactamente igual para el usuario final, pero sin los riesgos operativos/de seguridad más evidentes.

---

## Fase 1 — Fundación de la API única y seguridad base

**Objetivo**: sentar las bases de `02_Arquitectura.md` sin romper el flujo actual.

- Introducir Redis (cache, rate limiting distribuido, denylist JWT — ADR-005).
- Implementar refresh tokens con rotación (ADR-007) en FastAPI.
- Introducir el gateway (Nginx/Traefik) delante de los servicios existentes (`08_Docker.md`).
- Normalizar RBAC (`roles`/`permisos`) sin cambiar el comportamiento visible aún (equivalente funcional a los 3 roles actuales).
- Laravel empieza a consumir `/auth/login` de la API en vez de `password_verify` propio (primer paso de ADR-002, acotado solo a autenticación).

**Punto estable de cierre**: mismo producto visible hoy, pero con autenticación unificada, rate limiting robusto y gateway operativo. Nada del frontend cambia para el usuario final.

---

## Fase 2 — Migración del dominio de datos (esquema objetivo)

**Objetivo**: implementar `03_BaseDatos.md` completo.

- Alembic asume la propiedad del esquema de negocio (ADR-003).
- Migración de datos `pacientes`/`infantes` → `ninos` (ADR-006), con reconstrucción de `padre_id` (ver riesgo asociado en `15_Riesgos.md`).
- Implementación real de `alertas`, `alergias`, `notas_nutriologo`, `menus_semanales` (hoy tablas cáscara, hallazgo #2).
- Laravel y Flask migran el resto de sus escrituras de negocio (pacientes, evaluaciones, menús, reportes, citas) a consumir la API en vez de Eloquent directo (completa ADR-002).

**Punto estable de cierre**: el mismo producto web funcional actual, ahora corriendo sobre el esquema de datos objetivo, sin pérdida de datos existentes.

---

## Fase 3 — Dominio de gamificación (backend) sin app móvil todavía

**Objetivo**: implementar en la API el dominio de `03_BaseDatos.md` §6 (hábitos, retos, logros, puntos, recompensas) y sus endpoints (`04_API.md`), verificable vía `/docs` (Swagger) y/o un cliente de pruebas, antes de invertir en la app móvil.

- Catálogos de hábitos/retos/logros/recompensas gestionables desde el panel Laravel (nutriólogo/admin).
- Lógica de cálculo de puntos, rachas y condición de retos, cubierta por pruebas (`10_Pruebas.md`).

**Punto estable de cierre**: la API expone y valida completamente el dominio de gamificación; el producto web sigue funcionando igual, ahora con capacidad de que un nutriólogo asigne hábitos/retos a un niño desde el panel, aunque el niño todavía no tenga una app para verlos.

---

## Fase 4 — App móvil (MVP)

**Objetivo**: implementar `07_AppMovil.md` — MVP con Login PIN, Home, Hábitos del día, Avatar básico, Puntos. Retos/Logros/Recompensas pueden entrar en un incremento inmediatamente posterior si el tiempo no alcanza para todo en el mismo hito, pero **login + hábitos + puntos + avatar es el corte mínimo viable**, no negociable a menos.

- Vinculación de dispositivo (`codigo_vinculacion`).
- Modo Padre básico (ver progreso descriptivo).
- Publicación en modo prueba (TestFlight / Internal Testing de Play Console) antes de tienda pública.

**Punto estable de cierre**: un padre puede vincular a su hijo, el niño puede loguearse con PIN y marcar hábitos del día, ganando puntos visibles — ciclo completo funcional de punta a punta.

---

## Fase 5 — Gamificación completa + notificaciones

**Objetivo**: completar retos, logros, recompensas y notificaciones push (`07_AppMovil.md` §9, mensajería futura de `02_Arquitectura.md`).

**Punto estable de cierre**: app móvil con la experiencia de gamificación completa descrita en `07_AppMovil.md`.

---

## Fase 6 — Migración de la web pública a Next.js (opcional según recursos)

**Objetivo**: ejecutar `06_Web.md` §3.2 con estrategia *strangler fig* (página por página).

**Punto estable de cierre**: cada página migrada convive con las que aún sirve Flask sin romper navegación ni SEO; el corte final (apagar Flask) es un paso explícito y reversible al final de esta fase, no un big-bang.

---

## Fase 7 — Escala en la nube (Etapa 2 de `09_Cloud.md`)

**Objetivo**: pasar a infraestructura cloud empresarial solo si se cumplen los criterios de disparo de `09_Cloud.md` §1 — esta fase **no tiene fecha fija**, se activa por criterio de tráfico/negocio, no por calendario.

**Punto estable de cierre**: mismo producto, infraestructura capaz de sostener el volumen que disparó la fase, con monitoreo (`05_Seguridad.md` §8) y balanceo (`09_Cloud.md` §2) operando en producción real.

---

## Resumen visual

```
Fase 0 ─▶ Fase 1 ─▶ Fase 2 ─▶ Fase 3 ─▶ Fase 4 ─▶ Fase 5 ─▶ Fase 6 (opcional/paralela) ─▶ Fase 7 (por criterio de escala)
estabilizar  fundación  esquema    gamif.    app móvil  gamif.      web→Next.js              cloud empresarial
             API+seg.   de datos   backend   MVP        completa
```

Cada flecha representa un punto estable verificado (checklist de cierre de fase en `13_Backlog.md`), no una fecha calendario fija — el ritmo real depende del equipo/agentes disponibles en cada momento, documentado y actualizado en `EstadoProyecto.md`.
