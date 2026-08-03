# Fase — Modernización web NutriKids (2026-07-30)

Evolución incremental sobre la arquitectura existente (Laravel admin/nutriólogo + Flask padres + FastAPI).

## Componentes Blade nuevos

| Componente | Ruta |
|------------|------|
| `stat-card` | `resources/views/components/stat-card.blade.php` |
| `page-header` | `resources/views/components/page-header.blade.php` |
| `empty-state` | `resources/views/components/empty-state.blade.php` |

## Servicios Laravel nuevos

| Servicio | Descripción |
|----------|-------------|
| `App\Services\AuditLogService` | Agrega actividad reciente sin tabla dedicada |

## Controladores Admin nuevos

| Controlador | Rutas |
|-------------|-------|
| `Admin\NutriologoController` | `admin.nutriologos.*` |
| `Admin\EstadisticasController` | `admin.estadisticas.index` |
| `Admin\AuditoriaController` | `admin.auditoria.index` |
| `Admin\InstitucionController` | `admin.instituciones.*` |

## Vistas Admin nuevas / actualizadas

- `admin/dashboard.blade.php` — KPIs clínicos, accesos rápidos, componentes reutilizables
- `admin/nutriologos/*` — datos reales desde Eloquent
- `admin/estadisticas/index.blade.php` — gráficas Chart.js
- `admin/auditoria/index.blade.php` — bitácora agregada
- `admin/instituciones/index.blade.php` — registro en `storage/app/instituciones.json`
- `admin/partials/navigation.blade.php` — enlaces a nuevos módulos

## Portal Nutriólogo

- Dashboard: cumplimiento de planes, recomendaciones recientes
- Expediente paciente: gráfica IMC, reportes clínicos

## Portal Padre (Flask)

| Ruta | Vista |
|------|-------|
| `/portal` | `portal/dashboard.html` |
| `/portal/hijos` | `portal/hijos.html` |
| `/portal/hijos/<id>` | `portal/hijo_detalle.html` |
| `/portal/citas` | `portal/citas.html` |
| `/portal/habitos` | `portal/habitos.html` |
| `/portal/notificaciones` | `portal/notificaciones.html` |
| `/portal/cuenta` | `portal/cuenta.html` |

- Layout: `portal/layout.html` + `static/CSS/portal.css`
- Datos vía API v1 (`/api/v1/ninos`, `/evaluaciones`, `/citas`, `/habitos`)
- **Sin gamificación** en portal web (solo app móvil infantil)

## Decisiones de diseño

1. No se reemplazó Laravel ni Flask; se extendieron.
2. Instituciones en JSON local hasta migración formal (evita cambio de esquema MySQL/Postgres en Laravel).
3. Auditoría agregada desde tablas existentes; pendiente tabla `audit_logs` en API.
4. Portal padre consume FastAPI v1 con JWT de sesión Flask (alineado con `06_Web.md`).

## Próxima fase recomendada

- Migrar escrituras Laravel → `FastApiClient` (T1.5 / ADR-002)
- Tabla `instituciones` en BD + API v1
- Gráficas IMC en portal padre (Chart.js)
- RBAC por fila en nutriólogo (solo pacientes asignados)
