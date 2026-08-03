<?php

namespace App\Models;

use App\Services\Nutricion\AntropometriaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluacion extends Model
{
    protected $table = 'evaluaciones';

    protected $fillable = [
        'paciente_id',
        'nino_id',
        'nutriologo_id',
        'peso',
        'talla',
        'peso_kg',
        'talla_cm',
        'imc',
        'percentil_oms',
        'fecha_evaluacion',
        'recomendaciones',
    ];

    protected $casts = [
        'peso_kg' => 'decimal:2',
        'talla_cm' => 'decimal:2',
        'imc' => 'decimal:2',
        'fecha_evaluacion' => 'date',
    ];

    /**
     * La tabla arrastra dos representaciones de la medición: `peso`/`talla` en texto
     * (formulario web histórico) y `peso_kg`/`talla_cm`/`imc` numéricas (API y app
     * móvil). Derivarlas aquí evita que un registro creado desde la web quede
     * invisible para los clientes que leen las columnas numéricas.
     */
    protected static function booted(): void
    {
        static::saving(function (self $evaluacion): void {
            $evaluacion->derivarMedidasNumericas();
            $evaluacion->heredarNinoDelPaciente();
        });
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function nino(): BelongsTo
    {
        return $this->belongsTo(Nino::class, 'nino_id');
    }

    public function nutriologo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nutriologo_id');
    }

    protected function derivarMedidasNumericas(): void
    {
        $antropometria = app(AntropometriaService::class);

        $peso = $antropometria->normalizeDecimal((string) $this->peso);
        $talla = $antropometria->normalizeDecimal((string) $this->talla);

        if ($peso !== null && $peso > 0) {
            $this->peso_kg = round($peso, 2);
        }

        // El formulario acepta la talla en metros (1.18) o en centímetros (118).
        $tallaMetros = $antropometria->tallaMetros($talla);
        if ($tallaMetros !== null && $tallaMetros > 0) {
            $this->talla_cm = round($tallaMetros * 100, 2);
        }

        $imc = $antropometria->calculateImc($peso, $talla);
        if ($imc !== null && $imc > 0) {
            $this->imc = $imc;
        }

        if ($this->fecha_evaluacion === null) {
            $this->fecha_evaluacion = now()->toDateString();
        }
    }

    /**
     * La API y la app móvil consultan las mediciones por `nino_id`. El panel web
     * sólo conoce el expediente, así que copiamos el enlace del paciente para que
     * el padre vea en su teléfono lo que registró el nutriólogo.
     */
    protected function heredarNinoDelPaciente(): void
    {
        if ($this->paciente_id === null) {
            return;
        }

        // Se recalcula también al reasignar la medición a otro expediente.
        if ($this->nino_id !== null && ! $this->isDirty('paciente_id')) {
            return;
        }

        $this->nino_id = Paciente::whereKey($this->paciente_id)->value('nino_id');
    }
}
