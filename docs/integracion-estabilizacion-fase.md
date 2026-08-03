# Integración, estabilización y optimización — Informe técnico

**Fecha:** 2026-07-30  
**Alcance:** Fases 1–8 (auditoría + correcciones prioritarias de integración)  
**Restricción:** Sin cambio de arquitectura general ni eliminación de funcionalidades.

---

## 1. Diagnóstico inicial

NutriKids opera como plataforma multi-módulo:

| Módulo | Stack | Rol |
|--------|-------|-----|
| API central | FastAPI (`/api/v1/*`) | JWT, RBAC, datos clínicos, gamificación |
| Admin / Nutriólogo | Laravel 11 | Paneles staff |
| Portal padre / público | Flask | UI familiar + sitio informativo |
| App móvil | Expo / React Native | Padres e interacción infantil |

**Estado previo:** módulos funcionales de forma aislada, con rutas legacy (`/api/*`), datos en caché local sin sincronizar, puertos inconsistentes entre `.env` y Docker, y lógica duplicada de antropometría en Laravel.

---

## 2. Problemas encontrados (auditoría)

### Críticos
1. **`habitsRepository.loadRegistros`** (móvil): en modo producción leía solo `localStorage`, ignorando la API.
2. **Flask login/registro/citas** usaban rutas legacy (`/api/auth/*`, `/api/citas`) en lugar de `/api/v1/*`.
3. **Puertos por defecto** desalineados: `8001`/`5001` en ejemplos vs `8000`/`5000`/`8080` en Docker.

### Altos
4. **IMC duplicado** en `DashboardController` y `ReporteController` vs `AntropometriaService`.
5. **Portal Flask** ocultaba errores de API devolviendo listas vacías sin feedback al usuario.
6. **`EXPO_PUBLIC_DEMO_MODE=true`** por defecto en móvil.

### Medios
7. **`FastApiClient.php`** definido pero no usado en controladores Laravel (deuda técnica).
8. **Entidades `ninos` (API/móvil) ≠ `pacientes` (Laravel)** sin sincronización automática.
9. **Código muerto:** carpeta `web1_flask/` (template duplicado).
10. **Cobertura de pruebas** mínima en flujos críticos.

---

## 3. Cambios realizados

### App móvil
- Nuevo endpoint consumido: `GET /api/v1/ninos/{id}/habitos/registros`.
- `habitsApi.getRegistros()` + `habitsRepository.loadRegistros()` sincroniza API → caché local.
- `.env.example`: `EXPO_PUBLIC_DEMO_MODE=false`.
- Test Jest: `habitsRepository.test.ts`.

### FastAPI
- `GamificacionService.list_habito_registros()`.
- Ruta `GET /api/v1/ninos/{nino_id}/habitos/registros`.

### Flask
- Defaults alineados: API `8000`, Laravel `8080`.
- `api_v1_post()` helper.
- Login → `/api/v1/auth/login` (+ `refresh_token` en sesión padre).
- Registro → `/api/v1/auth/register`.
- Solicitar cita → `/api/v1/citas`.
- Eliminado fallback legacy en lectura de citas.
- Helpers `_fetch_*` devuelven `(datos, error)`; banner `api_error` en layout del portal.

### Laravel
- `DashboardController` y `ReporteController` inyectan `AntropometriaService` (eliminada duplicación IMC).
- `config/services.php`: default API `8000`.
- Test PHPUnit: `AntropometriaServiceTest`.

### Limpieza
- Eliminado `web1_flask/templates/calculadora.html`.
- `.env.example` (raíz y Flask): puertos unificados.

---

## 4. Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `fastapi/app/services/gamificacion_service.py` | Listado registros hábitos |
| `fastapi/app/api/v1/endpoints/operaciones.py` | GET registros |
| `NutriKidsMovil/src/features/habitos/repositories/habitsApi.ts` | `getRegistros` |
| `NutriKidsMovil/src/features/habitos/repositories/habitsRepository.ts` | Sync API |
| `NutriKidsMovil/.env.example` | Demo mode false |
| `flask/app.py` | v1 auth/citas, errores portal, puertos |
| `flask/templates/portal/layout.html` | Banner api_error |
| `flask/.env.example` | Puertos |
| `config/services.php` | Puerto API |
| `.env.example` | FLASK 5000 |
| `app/Http/Controllers/Nutriologo/DashboardController.php` | AntropometriaService |
| `app/Http/Controllers/Nutriologo/ReporteController.php` | AntropometriaService |
| `tests/Unit/AntropometriaServiceTest.php` | Nuevo |
| `NutriKidsMovil/.../habitsRepository.test.ts` | Nuevo |
| `web1_flask/templates/calculadora.html` | Eliminado |

---

## 5. Riesgos pendientes

| Riesgo | Impacto | Mitigación recomendada |
|--------|---------|------------------------|
| `ninos` ≠ `pacientes` | Nutriólogo no ve hijos registrados solo en móvil | Job de sync o endpoint de vinculación padre→paciente |
| `FastApiClient` sin uso | Laravel no consume API para datos compartidos | Integrar en módulos que requieran datos cross-stack |
| Rutas legacy FastAPI (`/api/*`) aún activas | Confusión en clientes | Deprecar y documentar sunset |
| Sin E2E automatizado completo | Regresiones en login/citas | Pipeline CI con pytest + Dusk/Playwright |
| Refresh token Flask no renovado automáticamente | Sesión padre expira | Middleware Flask que renueve con `/api/v1/auth/refresh` |

---

## 6. Recomendaciones — siguiente fase

1. **Sincronización clínica:** mapear `nino_id` ↔ `paciente_id` cuando el nutriólogo asigna expediente.
2. **Estados UI móvil:** loading/error/empty/timeout en todas las pantallas (patrón ya iniciado en hábitos).
3. **Pruebas funcionales E2E:** registro, login, pacientes, evaluaciones, menús, usuarios admin.
4. **Deprecación legacy API:** migrar Flask comunidad (`/api/discusiones`) a v1.
5. **Observabilidad:** health checks unificados y alertas si Flask no alcanza FastAPI.
6. **Performance Laravel:** eager loading en dashboards; cache de catálogos RBAC.

---

## 7. Verificación sugerida

```bash
# Docker
docker compose up -d
docker compose exec fastapi pytest tests/ -q
docker compose exec laravel php artisan test --filter=AntropometriaServiceTest

# Flask login padre → /portal
curl -X POST http://localhost:5000/IniciarSesion -H "Content-Type: application/json" \
  -d '{"email":"padre@nutrikids.com","contrasena":"Padre123*"}'

# API registros hábitos (con token padre)
curl http://localhost:8000/api/v1/ninos/1/habitos/registros -H "Authorization: Bearer <token>"
```

---

## 8. Criterios de aceptación — estado

| Criterio | Estado |
|----------|--------|
| Consumo API real (módulos tocados) | ✅ Parcial — móvil hábitos, Flask auth/citas/portal |
| Sin errores críticos conocidos | ✅ Bugs P1 corregidos |
| Sin duplicación innecesaria (IMC) | ✅ Centralizado en servicio |
| Sincronización cross-módulo | ⚠️ Pendiente ninos↔pacientes |
| Compilación / tests | ✅ Tests unitarios añadidos |
| Documentación actualizada | ✅ Este informe |

**Detener aquí y esperar nuevas instrucciones para fase de sincronización clínica y E2E completo.**
