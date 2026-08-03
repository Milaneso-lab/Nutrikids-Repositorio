# Estabilización de la capa de persistencia

Informe de la fase cuyo objetivo era que **todas las operaciones CRUD escriban y lean de
PostgreSQL**, sin datos simulados, y que la interfaz confirme cada operación al usuario.

---

## 1. Diagnóstico inicial

Los síntomas reportados eran «crear, editar y eliminar usuarios no cambia nada en la base de
datos». La auditoría encontró que el problema no era uno solo, sino cinco fallos independientes
que se manifestaban igual desde fuera.

La conexión a PostgreSQL, el pool y las transacciones estaban bien configurados en los tres
servicios (Laravel, FastAPI y Flask). El problema estaba por encima: en los modelos, en los
controladores y en la configuración del cliente móvil.

## 2. Causas raíz

### 2.1 `usuarios.rol_id` era NOT NULL y nadie lo rellenaba

La tabla `usuarios` guarda el rol dos veces: como texto en `rol` y como clave foránea en
`rol_id`. Los controladores sólo escribían `rol`, así que cada alta terminaba en:

```
SQLSTATE[23502]: Not null violation: null value in column "rol_id" violates not-null constraint
```

La excepción se producía antes de que el controlador pudiera capturarla, así que el usuario veía
un 500 sin mensaje y `storage/logs/laravel.log` quedaba vacío. **Ésta era la causa del síntoma
principal.**

### 2.2 Los formularios del panel devolvían JSON crudo

Varios controladores respondían siempre con `response()->json(...)`. Como los formularios Blade
se envían con POST normal, el navegador mostraba el volcado JSON en pantalla en lugar de volver
al panel con un mensaje. Al usuario le parecía que la operación no se había ejecutado.

### 2.3 Dos módulos no escribían en PostgreSQL

- `ConfiguracionController::update` devolvía «guardado correctamente» sin persistir nada.
- `InstitucionController` guardaba el catálogo en `storage/app/instituciones.json`.
- `ContenidoController::responderContacto` confirmaba el envío de la respuesta sin guardarla.

### 2.4 Las mediciones del panel eran invisibles para la app

`evaluaciones` arrastra dos representaciones de la misma medición: `peso`/`talla` en texto
(formulario web histórico) y `peso_kg`/`talla_cm`/`imc` numéricas (API y app). El panel sólo
rellenaba las de texto, así que la API devolvía mediciones sin valores.

Más grave: el panel web trabaja sobre la tabla `pacientes` y la app móvil sobre `ninos`, que son
dos tablas distintas para la misma persona. No existía ningún enlace entre ellas, de modo que
**nada de lo que registraba el nutriólogo llegaba a la app del padre, ni al revés.**

### 2.5 La app móvil estaba en modo demo

`NutriKidsMovil/.env` tenía `EXPO_PUBLIC_DEMO_MODE=true`, así que la app usaba datos en memoria y
no llamaba a la API. Además `EXPO_PUBLIC_API_BASE_URL=http://localhost:8000` apunta al propio
teléfono cuando la app corre en un dispositivo físico.

## 3. Cambios realizados

### Sincronización de rol (causa 2.1)

`rol` y `rol_id` se derivan el uno del otro en el momento de guardar, en los dos backends, de modo
que ningún camino de escritura puede volver a dejar `rol_id` nulo:

- Laravel: hook `saving` en `App\Models\User` (`alinearRolYRolId`), con caché del mapa de roles.
- FastAPI: listeners `before_insert` / `before_update` de SQLAlchemy sobre `Usuario`.

### Respuestas y retroalimentación (causa 2.2, Fase 5)

Se creó el trait `App\Http\Controllers\Concerns\RespuestasCrud`, que decide según
`Request::expectsJson()`:

- **Éxito**: JSON para AJAX, o redirección con mensaje flash para formularios.
- **Validación**: 422 con la lista de errores, o vuelta al formulario conservando lo escrito.
- **Excepción**: registra el detalle técnico en el log y devuelve un mensaje accionable,
  traducido desde el `SQLSTATE` (registro duplicado, dato vinculado, falta un campo obligatorio,
  base de datos sin conexión). En producción no expone el mensaje interno.

En `resources/views/layouts/app.blade.php` se añadió el manejo global de formularios: deshabilita
el botón y muestra un spinner al enviar (evita el doble envío), y pide confirmación en las
acciones marcadas con `data-confirmar`.

### Datos que faltaban por persistir (causa 2.3, Fase 4)

- Nueva tabla `configuraciones` (clave/valor) y modelo `Configuracion`.
- Nueva tabla `instituciones` y modelo `Institucion`.
- Nuevas columnas `contactos.respuesta`, `respondido_en` y `respondido_por_id`.

### Puente entre el panel y la app (causa 2.4, Fase 7)

- `Evaluacion` deriva `peso_kg`, `talla_cm` e `imc` desde el texto al guardar, aceptando la talla
  en metros o centímetros.
- Nueva columna `pacientes.nino_id` (única, FK a `ninos`) y modelo de lectura `App\Models\Nino`.
- El formulario de paciente permite vincular el expediente con un niño registrado en la app.
- `Evaluacion` y `Menu` heredan el `nino_id` del expediente al guardarse, y al vincular un
  expediente existente se propaga a su historial.
- Migraciones de relleno para el histórico ya guardado.

El detalle del modelo de datos está en [`flujo-nino-paciente.md`](flujo-nino-paciente.md).

### App móvil (causa 2.5)

- `EXPO_PUBLIC_DEMO_MODE=false`: la app trabaja contra PostgreSQL a través de la API.
- `env.ts` sustituye `localhost` por la IP desde la que Expo sirve el bundle, para que un teléfono
  físico alcance el backend sin editar el `.env`.
- `errorHandler.ts` entiende el sobre de error de la API (`{error: {code, message, details}}`).
  Antes lo ignoraba y mostraba «Ocurrió un error inesperado» en lugar de «Email ya registrado».
  Se añadieron los códigos `Conflict` (409) y `Unavailable` (503).

### Manejo global de errores (Fase 6)

- FastAPI: manejadores para `IntegrityError`, `OperationalError`, `SQLAlchemyError` y un
  `Exception` de último recurso, todos con el mismo contrato JSON y con el detalle técnico oculto
  fuera de depuración.
- FastAPI: nuevo `DELETE /api/v1/usuarios/{id}`, con guarda contra autoeliminación.
- Laravel: centralizado en `RespuestasCrud`.

## 4. Archivos modificados

**Laravel — controladores**

- `app/Http/Controllers/Concerns/RespuestasCrud.php` *(nuevo)*
- `app/Http/Controllers/Admin/UsuarioController.php`
- `app/Http/Controllers/Admin/NutriologoController.php`
- `app/Http/Controllers/Admin/RolController.php`
- `app/Http/Controllers/Admin/ConfiguracionController.php`
- `app/Http/Controllers/Admin/InstitucionController.php`
- `app/Http/Controllers/Admin/ContenidoController.php`
- `app/Http/Controllers/Nutriologo/PacienteController.php`
- `app/Http/Controllers/Nutriologo/EvaluacionController.php`
- `app/Http/Controllers/Nutriologo/MenuController.php`

**Laravel — modelos**

- `app/Models/User.php`, `app/Models/Evaluacion.php`, `app/Models/Menu.php`,
  `app/Models/Paciente.php`, `app/Models/Contacto.php`
- `app/Models/Nino.php`, `app/Models/Configuracion.php`, `app/Models/Institucion.php` *(nuevos)*

**Laravel — vistas**

- `resources/views/layouts/app.blade.php`
- `resources/views/admin/usuarios/create.blade.php`, `edit.blade.php`
- `resources/views/admin/configuracion/index.blade.php`
- `resources/views/nutriologo/pacientes/create.blade.php`, `edit.blade.php`

**FastAPI**

- `fastapi/models.py`, `fastapi/app/core/handlers.py`,
  `fastapi/app/services/usuario_service.py`, `fastapi/app/api/v1/endpoints/identidad.py`

**App móvil**

- `NutriKidsMovil/.env`, `src/core/config/env.ts`,
  `src/core/errors/errorHandler.ts`, `AppError.ts`, `friendlyMessages.ts`

**QA y documentación**

- `scripts/qa/verificar_persistencia.py` *(nuevo)*
- `docs/estabilizacion-persistencia.md` *(este documento)*, `docs/flujo-nino-paciente.md`

## 5. Migraciones nuevas

| Migración | Qué hace |
| --- | --- |
| `2026_07_31_190000_backfill_evaluaciones_medidas_numericas` | Rellena `peso_kg`, `talla_cm` e `imc` del histórico a partir del texto |
| `2026_07_31_191000_create_configuraciones_e_instituciones_tables` | Crea `configuraciones` e `instituciones` |
| `2026_07_31_192000_enlazar_pacientes_con_ninos` | Añade `pacientes.nino_id`, empareja el histórico y propaga el enlace a `evaluaciones` y `menus` |
| `2026_07_31_193000_agregar_respuesta_a_contactos` | Añade `respuesta`, `respondido_en` y `respondido_por_id` |

Todas son idempotentes y seguras de reejecutar. Los rellenos de datos no se revierten en `down()`
para no destruir mediciones válidas.

## 6. Variables de entorno

No hay variables nuevas en el backend. Las que importan para esta fase:

| Variable | Dónde | Valor |
| --- | --- | --- |
| `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | `.env` (Laravel) | `pgsql` contra el servicio `postgres` |
| `NUTRIKIDS_DATABASE_URL` | FastAPI | Cadena SQLAlchemy hacia PostgreSQL |
| `EXPO_PUBLIC_DEMO_MODE` | `NutriKidsMovil/.env` | **`false`**. En `true` la app no toca la base de datos |
| `EXPO_PUBLIC_API_BASE_URL` | `NutriKidsMovil/.env` | `http://localhost:8000`; la app lo reescribe sola con la IP de Expo. Para fijarlo, usar la IP Wi-Fi de la PC |

## 7. Pruebas ejecutadas

`scripts/qa/verificar_persistencia.py` ejecuta el CRUD real contra la API, contra el panel web y
comprueba en PostgreSQL que cada operación dejó la fila esperada. Los datos que crea usan el
dominio `@qanutrikids.com` y se eliminan al terminar.

```powershell
$OutputEncoding = [System.Text.Encoding]::UTF8
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
Get-Content scripts/qa/verificar_persistencia.py -Encoding UTF8 | docker compose exec -T fastapi python -
```

Cubre, con verificación en base de datos de cada paso:

- **API**: alta, edición y baja de usuario; rechazo de correo duplicado con mensaje legible;
  rechazo sin token.
- **Panel admin**: alta, edición y baja de usuario; sincronización de `rol_id` al cambiar el rol;
  error de duplicado en HTML conservando lo escrito; configuración; instituciones.
- **Panel nutriólogo**: alta y edición de paciente; registro de mediciones con columnas numéricas
  y autor; alta y actualización de plan alimenticio.
- **Ida y vuelta web ↔ móvil**: el padre registra al niño desde la API, el nutriólogo abre y
  vincula su expediente en la web, registra una medición y un plan, y ambos se recuperan desde la
  app filtrando por `nino_id`; el niño aparece en el panel y la edición desde la app persiste.
- **Módulos restantes**: roles, contactos y su respuesta, consultas (citas), actividades
  (hábitos y su registro), recompensas y logros, notificaciones (alertas).
- **Pantallas**: las 15 vistas de los dos paneles responden 200.
- **Coherencia global**: ninguna evaluación sin columnas numéricas, ningún usuario sin `rol_id` ni
  desincronizado, ninguna medición o plan de un expediente vinculado invisible para la app.

Además: `pytest` en FastAPI (10 pruebas), `jest` y `tsc --noEmit` en la app móvil.

## 8. Resultados

```
RESULTADO: 57/57 comprobaciones correctas
```

Todo lo pedido en las verificaciones obligatorias queda cubierto y en verde: crear, editar y
eliminar usuario; iniciar sesión; crear y editar paciente; registrar mediciones; crear y
actualizar plan alimenticio; consultar la misma información desde la web y desde la app.

## 9. Riesgos pendientes

1. **Dos tablas para el mismo niño.** El puente `pacientes.nino_id` resuelve la visibilidad, pero
   la duplicidad sigue ahí. Un expediente sin vincular continúa siendo invisible para la app: es
   el comportamiento esperado hoy, pero conviene unificar.
2. **El vínculo es manual.** El nutriólogo debe elegir el niño en el formulario. Si no lo hace, no
   hay aviso; el expediente simplemente queda desconectado.
3. **Notificaciones y mensajes familiares viven en el dispositivo.** El módulo `comunicacion` de la
   app usa almacenamiento local, no PostgreSQL. Es coherente con los recordatorios locales, pero
   los mensajes de la familia no se sincronizan entre dispositivos ni son visibles para el
   nutriólogo. `communicationApi.registerDeviceToken` llama a `/dispositivos/registrar-token`, que
   no existe todavía en la API; el fallo se ignora y el token queda local.
4. **Laravel no tiene suite de pruebas.** `php artisan test` no está disponible en el contenedor,
   así que la cobertura de regresión del panel depende del script de QA.
5. **`infantes` sigue vacía.** Es una tabla muerta que confunde al leer el esquema.
6. **Los catálogos de alimentos y recetas del panel admin son estáticos** (`admin/contenido/alimentos`
   y `recetas` sirven vistas sin modelo). No entran en el CRUD verificado.

## 10. Recomendaciones para la siguiente fase

1. **Unificar `pacientes` en `ninos`.** Mover las columnas clínicas a `ninos` o a una tabla
   `expedientes` con `nino_id` como clave primaria, y retirar `paciente_id` de `evaluaciones`,
   `menus` y `reportes`. Eliminar `infantes`.
2. **Sugerir el vínculo automáticamente.** Al crear un expediente, proponer el niño que coincida
   por nombre y fecha de nacimiento, y avisar en el panel cuando un expediente quede sin vincular.
3. **Llevar las notificaciones a PostgreSQL.** Crear la tabla y los endpoints de mensajes
   familiares, e implementar `/dispositivos/registrar-token` para el push real.
4. **Instalar PHPUnit y convertir el script de QA en pruebas de integración** que corran en CI, de
   modo que las invariantes de coherencia se comprueben en cada cambio.
5. **Revisar los catálogos estáticos** de alimentos y recetas: darles modelo y tabla, o retirarlos
   del panel mientras no tengan datos reales.
