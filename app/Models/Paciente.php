<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    public const ESTADO_ACTIVO = 'activo';

    public const ESTADO_SEGUIMIENTO = 'seguimiento';

    public const ESTADO_INACTIVO = 'inactivo';

    public const ESTADO_ALTA = 'alta';

    protected $table = 'pacientes';

    protected $fillable = [
        'nino_id',
        'nombre',
        'apellidos',
        'fecha_nacimiento',
        'estado_paciente',
        'historia_clinica',
        'antecedentes',
        'alergias',
        'objetivo_nutricional',
        'notas_seguimiento',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'datetime',
    ];

    /**
     * Niño de la app móvil al que corresponde este expediente. Cuando está
     * enlazado, las mediciones y los planes que registra el nutriólogo se
     * vuelven visibles para el padre desde la aplicación.
     */
    public function nino()
    {
        return $this->belongsTo(Nino::class, 'nino_id');
    }

    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'paciente_id');
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'paciente_id');
    }

    public function menus()
    {
        return $this->hasMany(Menu::class, 'paciente_id');
    }
}
