# Portal del Nutriólogo — Fase 2 (evolución clínica)

**Fecha:** 2026-07-30  
**Alcance:** Extensión del portal Laravel existente sin reemplazar funcionalidades.

## Diagnóstico inicial

| Área | Estado previo |
|------|----------------|
| Dashboard | KPIs básicos, gráfica IMC, citas parciales |
| Pacientes | Listado sin búsqueda/filtros; expediente en una sola página |
| Citas | Bandeja dual; sin calendario |
| Menús | CRUD sin estado, duplicado ni historial |
| Reportes | Generación + PDF; sin resumen visual |
| Perfil nutriólogo | No existía |
| Recomendaciones | Solo campo en evaluaciones |

## Cambios realizados

### Servicios
- `App\Services\Nutricion\AntropometriaService` — IMC, clasificación, series antropométricas, cumplimiento.

### Controladores nuevos
- `Nutriologo\PerfilController` — datos profesionales y foto.
- `Nutriologo\RecomendacionController` — listado y registro de recomendaciones.

### Controladores extendidos
- `DashboardController` — consultas del día, pacientes activos, actividad reciente.
- `PacienteController` — búsqueda, filtros, orden, expediente por pestañas.
- `CitaController` — vista agenda/calendario y recordatorios.
- `MenuController` — estado, duplicar, historial, filtros.
- `ReporteController` — estadísticas mensuales para gráfica.

### Componentes Blade
- `patient-status-badge`, `menu-estado-badge`, `list-toolbar`.

### Migración
- `2026_07_30_180000_extend_portal_nutriologo_clinico.php`
  - `pacientes`: estado clínico, historia, antecedentes, alergias, objetivo, notas.
  - `menus`: estado, duplicado_de_id.
  - `usuarios`: teléfono, especialidad, disponibilidad, foto_path.

## Rutas nuevas

| Método | Ruta | Nombre |
|--------|------|--------|
| GET | `/nutriologo/agenda` | `nutriologo.citas.agenda` |
| GET | `/nutriologo/perfil` | `nutriologo.perfil.index` |
| PUT | `/nutriologo/perfil` | `nutriologo.perfil.update` |
| GET | `/nutriologo/recomendaciones` | `nutriologo.recomendaciones.index` |
| POST | `/nutriologo/recomendaciones` | `nutriologo.recomendaciones.store` |
| POST | `/nutriologo/menus/{id}/duplicar` | `nutriologo.menus.duplicate` |

## Archivos creados

```
app/Services/Nutricion/AntropometriaService.php
app/Http/Controllers/Nutriologo/PerfilController.php
app/Http/Controllers/Nutriologo/RecomendacionController.php
database/migrations/2026_07_30_180000_extend_portal_nutriologo_clinico.php
resources/views/components/patient-status-badge.blade.php
resources/views/components/menu-estado-badge.blade.php
resources/views/components/list-toolbar.blade.php
resources/views/nutriologo/pacientes/_evaluaciones-table.blade.php
resources/views/nutriologo/citas/agenda.blade.php
resources/views/nutriologo/perfil/index.blade.php
resources/views/nutriologo/recomendaciones/index.blade.php
docs/web-portal-nutriologo-fase2.md
```

## Archivos modificados

```
app/Models/Paciente.php, Menu.php, User.php
app/Http/Controllers/Nutriologo/DashboardController.php
app/Http/Controllers/Nutriologo/PacienteController.php
app/Http/Controllers/Nutriologo/CitaController.php
app/Http/Controllers/Nutriologo/MenuController.php
app/Http/Controllers/Nutriologo/ReporteController.php
routes/web.php
resources/views/nutriologo/partials/navigation.blade.php
resources/views/nutriologo/dashboard.blade.php
resources/views/nutriologo/pacientes/index.blade.php
resources/views/nutriologo/pacientes/show.blade.php
resources/views/nutriologo/pacientes/edit.blade.php
resources/views/nutriologo/citas/index.blade.php
resources/views/nutriologo/menus/index.blade.php
resources/views/nutriologo/menus/create.blade.php
resources/views/nutriologo/menus/edit.blade.php
resources/views/nutriologo/reportes/index.blade.php
```

## Mejoras por módulo

- **Dashboard:** pacientes activos, consultas hoy, actividad reciente, alertas ampliadas.
- **Pacientes:** toolbar búsqueda/filtro/orden, badges de estado, acciones rápidas, paginación.
- **Expediente:** 10 pestañas (general, historia, antropometría, antecedentes, hábitos, consultas, planes, recomendaciones, documentos, seguimiento).
- **Evolución:** gráficas IMC, peso, talla y percentil estimado (Chart.js).
- **Planes:** estado activo/borrador/archivado, duplicar, historial por paciente.
- **Recomendaciones:** módulo dedicado + registro desde expediente (portal padre vía evaluaciones).
- **Agenda:** calendario mensual, recordatorios, estados de cita.
- **Reportes:** gráfica de barras por mes.
- **Perfil:** datos, especialidad, contacto, foto, disponibilidad.

## Verificación

Ejecutar migración:

```bash
php artisan migrate
# o vía Docker:
docker compose exec laravel php artisan migrate
```

Comprobar rutas nutriólogo, login con rol `nutriologo`, generación PDF de reportes y bandeja de citas.

## Recomendaciones — siguiente fase

1. Percentiles CDC exactos por edad/sexo en cada evaluación (helper JS completo).
2. Vincular citas con pacientes (`paciente_id` en citas).
3. Tablas `notas_nutriologo`, `alergias` existentes en esquema FastAPI → migrar a Laravel.
4. API REST para portal padre consumiendo recomendaciones en tiempo real.
5. Tests Feature para filtros de pacientes y duplicado de menús.
6. Exportación CSV/PDF avanzada en reportes.
