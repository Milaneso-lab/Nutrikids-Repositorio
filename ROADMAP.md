# Roadmap — NutriKids

> Documento de referencia rápida. Detalle completo en [`ProyectoIA/DocumentacionProyecto/12_Roadmap.md`](ProyectoIA/DocumentacionProyecto/12_Roadmap.md) y tareas en [`13_Backlog.md`](ProyectoIA/DocumentacionProyecto/13_Backlog.md).

## Estado actual: **Release Candidate 1.0.0-rc.1**

La plataforma web (Laravel + Flask + FastAPI) y la app móvil (auth, familia, progresión, hábitos, comunicación) están integradas y documentadas para entrega académica/profesional.

---

## Completado

| Fase / Épica | Estado |
|--------------|--------|
| Fase 0 — Estabilización Docker/secretos | Parcial (RC) |
| Fase 1 — API v1, JWT, RBAC, Redis | Implementado |
| Fase 4 móvil — T4.1 Auth | ✅ |
| Fase 4 móvil — T4.4a Centro familiar | ✅ |
| Fase 4 móvil — T4.4b Motor progresión | ✅ |
| Fase 4 móvil — T4.5 Hábitos | ✅ |
| Fase 4 móvil — T4.6 Comunicación | ✅ |
| RC — Consolidación y documentación | ✅ |

---

## Roadmap v2 (priorizado)

### P0 — Bloqueadores producción real

1. **T4.3** — Login PIN niño + vinculación dispositivo
2. **T1.4** — Gateway TLS (Nginx/Traefik)
3. **T1.5** — Laravel consume auth API exclusivamente
4. Migración Laravel/Flask a `/api/v1/*` (T2.4)
5. CI con PostgreSQL para tests de integración FastAPI

### P1 — Calidad y operaciones

6. **T4.2** — Cliente OpenAPI tipado en móvil
7. Endpoints API: mensajes, notificaciones push, historial hábitos
8. Upload avatar (foto niño)
9. Monitoreo: métricas, logs centralizados, alertas
10. E2E: Detox/Maestro para flujos críticos móvil

### P2 — Producto y datos

11. Migración `pacientes`/`infantes` → `ninos` (requiere decisión negocio `padre_id`)
12. Tablas clínicas reales: alertas, alergias, notas nutriólogo
13. Panel padre: configuración hábitos por niño
14. Mensajes nutriólogo → familia

### P3 — Mejoras

15. Lottie/confetti celebraciones
16. Leaderboard interno familia (no global)
17. PWA portal Flask
18. i18n (es/en)
19. Accesibilidad WCAG 2.1 AA audit completo
20. Eliminar navegación legacy móvil (`MainTabNavigator`, stubs)

---

## Criterios de salida v1.0 estable

- [ ] PIN niño operativo
- [ ] TLS en todos los servicios
- [ ] Sin seeds dev en producción verificado
- [ ] Cobertura tests >60% dominio crítico API
- [ ] E2E login padre → modo niño → registrar hábito
- [ ] Documentación alineada post-cada release
