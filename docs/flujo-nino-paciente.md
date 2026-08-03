# Flujo del niño a paciente

## Estado actual

El dominio tiene dos tablas para la misma persona real, cada una nacida de un cliente distinto:

| Tabla | La escribe | Contiene |
| --- | --- | --- |
| `ninos` | La app móvil, a través de la API (`/api/v1/ninos`) | Identidad del niño y su vida gamificada |
| `pacientes` | El panel web del nutriólogo (Laravel) | Expediente clínico: historia, antecedentes, objetivo |

`ninos` es la entidad canónica del dominio: `nino_habitos`, `nino_logros`, `nino_puntos`,
`nino_recompensas`, `nino_retos`, `alergias`, `alertas`, `notas_nutriologo` y `citas` cuelgan
de ella. `pacientes` es la tabla histórica del panel y sólo guarda el expediente.

La tabla `infantes` sigue vacía y sin columnas funcionales; no debe usarse.

## El puente: `pacientes.nino_id`

Un expediente puede vincularse a un niño mediante `pacientes.nino_id` (único: un expediente
por niño). El nutriólogo elige el niño desde el formulario de paciente, que sólo lista a los
que todavía no tienen expediente.

Cuando el expediente está vinculado:

1. `Evaluacion` y `Menu` copian el `nino_id` del expediente al guardarse (hook `saving` en cada
   modelo). Se recalcula también si la medición o el plan se reasignan a otro expediente.
2. Al vincular un expediente que ya tenía historial, `PacienteController::propagarVinculoAlHistorico`
   rellena el `nino_id` de las mediciones y planes anteriores.
3. La app del padre ve esos datos en `GET /api/v1/evaluaciones?nino_id=…` y
   `GET /api/v1/menus?nino_id=…`.

Sin vínculo, el expediente sigue funcionando igual que antes: es un registro sólo del panel.

## Reglas de integración

- Toda escritura clínica desde el panel pasa por los modelos `Evaluacion` y `Menu`; no insertar
  con `DB::table()`, porque se saltaría la propagación del `nino_id`.
- El alta y la baja de `ninos` ocurren en la API. El modelo `App\Models\Nino` de Laravel es de
  sólo lectura y existe para poder mostrar y vincular.
- `evaluaciones`, `menus` y `reportes` conservan las dos claves foráneas (`paciente_id` y
  `nino_id`) durante la transición. La API filtra por `nino_id`; el panel, por `paciente_id`.

## Restricciones

- No usar `infantes` como fuente clínica mientras siga vacía.
- No crear un segundo expediente para un niño que ya tiene uno: la restricción única lo impide y
  el formulario devuelve «Ese niño ya tiene un expediente asignado».
- Laravel no puede dar de alta un `nino`: `ninos.padre_id` es obligatorio y el formulario del
  nutriólogo no conoce al padre.

## Siguiente paso recomendado

Unificar en una sola tabla cuando el panel pueda operar directamente sobre `ninos`: mover las
columnas clínicas de `pacientes` a `ninos` (o a una tabla `expedientes` con `nino_id` como clave
primaria) y retirar `paciente_id` de `evaluaciones`, `menus` y `reportes`.
