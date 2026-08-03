<?php

namespace App\Services\Nutricion;

use App\Models\Evaluacion;
use App\Models\Paciente;
use Illuminate\Support\Collection;

class AntropometriaService
{
    public function normalizeDecimal(?string $value): ?float
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $normalized = str_replace(',', '.', trim($value));
        $normalized = preg_replace('/[^0-9.]/', '', $normalized);

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    public function tallaMetros(?float $talla): ?float
    {
        if ($talla === null || $talla <= 0) {
            return null;
        }

        return $talla > 3 ? $talla / 100 : $talla;
    }

    public function calculateImc(?float $peso, ?float $talla): ?float
    {
        $tallaMetros = $this->tallaMetros($talla);
        if (!$peso || !$tallaMetros) {
            return null;
        }

        return round($peso / ($tallaMetros * $tallaMetros), 2);
    }

    /** Clasificación pediátrica simplificada por IMC (referencia OMS aproximada). */
    public function classifyImc(?float $imc, ?int $edadAnios = null): string
    {
        if ($imc === null) {
            return 'sin_evaluacion';
        }

        if ($edadAnios !== null && $edadAnios < 18) {
            if ($imc < 14) {
                return 'bajo_peso';
            }
            if ($imc < 18.5) {
                return 'normal';
            }
            if ($imc < 25) {
                return 'sobrepeso';
            }

            return 'obesidad';
        }

        if ($imc < 18.5) {
            return 'bajo_peso';
        }
        if ($imc < 25) {
            return 'normal';
        }
        if ($imc < 30) {
            return 'sobrepeso';
        }

        return 'obesidad';
    }

    public function classifyLabel(string $code): string
    {
        return match ($code) {
            'bajo_peso' => 'Bajo peso',
            'normal' => 'Normal',
            'sobrepeso' => 'Sobrepeso',
            'obesidad' => 'Obesidad',
            default => 'Sin evaluación',
        };
    }

    public function edadAnios(Paciente $paciente): ?int
    {
        return $paciente->fecha_nacimiento?->age;
    }

    public function ultimaEvaluacion(Paciente $paciente): ?Evaluacion
    {
        return $paciente->evaluaciones->first()
            ?? $paciente->evaluaciones()->latest()->first();
    }

    public function resumenPaciente(Paciente $paciente): array
    {
        $ultima = $this->ultimaEvaluacion($paciente);
        $peso = $ultima ? $this->normalizeDecimal($ultima->peso) : null;
        $talla = $ultima ? $this->normalizeDecimal($ultima->talla) : null;
        $imc = $this->calculateImc($peso, $talla);
        $edad = $this->edadAnios($paciente);
        $clasificacion = $this->classifyImc($imc, $edad);

        return [
            'peso' => $peso,
            'talla_cm' => $talla !== null ? ($talla > 3 ? $talla : $talla * 100) : null,
            'imc' => $imc,
            'clasificacion' => $clasificacion,
            'clasificacion_label' => $this->classifyLabel($clasificacion),
            'ultima_fecha' => optional($ultima?->created_at)->format('d/m/Y H:i'),
        ];
    }

    /** @return array{labels: array, peso: array, talla: array, imc: array} */
    public function seriesAntropometricas(Collection $evaluaciones): array
    {
        $labels = [];
        $pesos = [];
        $tallas = [];
        $imcs = [];

        foreach ($evaluaciones->sortBy('created_at') as $ev) {
            $peso = $this->normalizeDecimal($ev->peso);
            $talla = $this->normalizeDecimal($ev->talla);
            $imc = $this->calculateImc($peso, $talla);
            if ($imc === null) {
                continue;
            }
            $labels[] = optional($ev->created_at)->format('d/m/Y') ?? '—';
            $pesos[] = $peso;
            $tallas[] = $talla !== null ? round($talla > 3 ? $talla : $talla * 100, 1) : null;
            $imcs[] = $imc;
        }

        return compact('labels', 'pesos', 'tallas', 'imcs');
    }

    public function cumplimientoObjetivo(Paciente $paciente): ?int
    {
        if (!$paciente->objetivo_nutricional) {
            return null;
        }

        $ultima = $this->ultimaEvaluacion($paciente);
        if (!$ultima) {
            return 0;
        }

        $clasificacion = $this->resumenPaciente($paciente)['clasificacion'];

        return match ($clasificacion) {
            'normal' => 100,
            'bajo_peso', 'sobrepeso' => 55,
            'obesidad' => 35,
            default => 20,
        };
    }
}
