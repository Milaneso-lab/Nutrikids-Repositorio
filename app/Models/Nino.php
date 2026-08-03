<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Niño registrado por un padre desde la aplicación móvil.
 *
 * Es la entidad canónica del dominio: los hábitos, logros, puntos, alertas y
 * citas cuelgan de ella. El panel web trabaja sobre `pacientes` (expediente
 * clínico), que se enlaza aquí mediante `pacientes.nino_id` para que lo que
 * registra el nutriólogo sea visible en la app del padre y viceversa.
 *
 * Laravel sólo lee esta tabla; el alta y la baja ocurren en la API.
 */
class Nino extends Model
{
    use SoftDeletes;

    protected $table = 'ninos';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'peso_actual_kg' => 'decimal:2',
        'talla_actual_cm' => 'decimal:2',
        'avatar_config' => 'array',
        'requiere_vinculacion_padre' => 'boolean',
    ];

    public function padre()
    {
        return $this->belongsTo(User::class, 'padre_id', 'id_usuario');
    }

    public function expediente()
    {
        return $this->hasOne(Paciente::class, 'nino_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellidos}");
    }

    /**
     * Niños que aún no tienen expediente clínico, más el ya enlazado al
     * expediente que se está editando. Alimenta el selector del formulario.
     *
     * @return \Illuminate\Support\Collection<int, static>
     */
    public static function seleccionables(?int $ninoIdActual = null)
    {
        return static::query()
            ->where(function ($q) use ($ninoIdActual) {
                $q->whereDoesntHave('expediente');

                if ($ninoIdActual) {
                    $q->orWhere('id', $ninoIdActual);
                }
            })
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get();
    }
}
