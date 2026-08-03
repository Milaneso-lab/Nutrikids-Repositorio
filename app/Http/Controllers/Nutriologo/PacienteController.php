<?php

namespace App\Http\Controllers\Nutriologo;

use App\Http\Controllers\Concerns\RespuestasCrud;
use App\Http\Controllers\Controller;
use App\Models\Nino;
use App\Models\Paciente;
use App\Services\Nutricion\AntropometriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PacienteController extends Controller
{
    use RespuestasCrud;

    public function __construct(private AntropometriaService $antropometria)
    {
    }

    /**
     * Un expediente sólo puede apuntar a un niño que no tenga otro expediente,
     * porque `pacientes.nino_id` es único.
     */
    private function reglas(?Paciente $paciente = null): array
    {
        $unico = 'unique:pacientes,nino_id'.($paciente ? ','.$paciente->id : '');

        return [
            'nombre' => 'required|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'required|date|before_or_equal:today',
            'estado_paciente' => 'nullable|in:activo,seguimiento,inactivo,alta',
            'nino_id' => ['nullable', 'integer', 'exists:ninos,id', $unico],
        ];
    }

    private function mensajes(): array
    {
        return [
            'nombre.required' => 'El nombre del paciente es obligatorio.',
            'fecha_nacimiento.required' => 'Indica la fecha de nacimiento.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'nino_id.exists' => 'El niño seleccionado ya no existe.',
            'nino_id.unique' => 'Ese niño ya tiene un expediente asignado.',
        ];
    }

    public function index(Request $request)
    {
        $query = Paciente::with(['evaluaciones' => fn ($q) => $q->latest()]);

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($builder) use ($term) {
                $builder->where('nombre', 'like', $term)
                    ->orWhere('apellidos', 'like', $term);
            });
        }

        if ($request->filled('estado') && Schema::hasColumn('pacientes', 'estado_paciente')) {
            $query->where('estado_paciente', $request->string('estado'));
        }

        $sort = $request->string('sort', 'nombre');
        match ($sort) {
            'reciente' => $query->latest(),
            'edad' => $query->orderBy('fecha_nacimiento'),
            default => $query->orderBy('nombre')->orderBy('apellidos'),
        };

        $pacientes = $query->paginate(15)->withQueryString();
        $resumenes = $pacientes->getCollection()->mapWithKeys(function (Paciente $p) {
            return [$p->id => $this->antropometria->resumenPaciente($p)];
        });

        return view('nutriologo.pacientes.index', compact('pacientes', 'resumenes'));
    }

    public function create()
    {
        return view('nutriologo.pacientes.create', ['ninos' => Nino::seleccionables()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());
        $validated['estado_paciente'] ??= Paciente::ESTADO_ACTIVO;
        // El selector envía cadena vacía cuando el expediente no se vincula.
        $validated['nino_id'] = ($validated['nino_id'] ?? null) ?: null;

        try {
            $paciente = Paciente::create($validated);
        } catch (Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'crear paciente');
        }

        return $this->respuestaExito(
            $request,
            $paciente->nino_id
                ? 'Paciente guardado y vinculado con la app del padre.'
                : 'Paciente guardado correctamente.',
            'nutriologo.pacientes.show',
            ['paciente' => $paciente->id],
            ['id' => $paciente->id],
        );
    }

    public function show(Paciente $id, Request $request)
    {
        $paciente = $id->load([
            'evaluaciones' => fn ($q) => $q->latest(),
            'menus' => fn ($q) => $q->latest(),
            'reportes' => fn ($q) => $q->latest(),
        ]);

        $resumen = $this->antropometria->resumenPaciente($paciente);
        $series = $this->antropometria->seriesAntropometricas($paciente->evaluaciones);
        $cumplimiento = $this->antropometria->cumplimientoObjetivo($paciente);
        $tab = $request->string('tab', 'general');

        $chartJson = json_encode([
            'labels' => $series['labels'],
            'imc' => $series['imcs'],
            'peso' => $series['pesos'],
            'talla' => $series['tallas'],
        ], JSON_UNESCAPED_UNICODE);

        return view('nutriologo.pacientes.show', compact(
            'paciente', 'resumen', 'chartJson', 'cumplimiento', 'tab'
        ));
    }

    public function edit(Paciente $id)
    {
        return view('nutriologo.pacientes.edit', [
            'paciente' => $id,
            'ninos' => Nino::seleccionables($id->nino_id),
        ]);
    }

    public function update(Request $request, Paciente $id)
    {
        $validated = $request->validate($this->reglas($id) + [
            'historia_clinica' => 'nullable|string',
            'antecedentes' => 'nullable|string',
            'alergias' => 'nullable|string',
            'objetivo_nutricional' => 'nullable|string|max:500',
            'notas_seguimiento' => 'nullable|string',
        ], $this->mensajes());

        $validated['nino_id'] = ($validated['nino_id'] ?? null) ?: null;

        try {
            $id->update($validated);
            $this->propagarVinculoAlHistorico($id);
        } catch (Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'actualizar expediente');
        }

        return $this->respuestaExito(
            $request,
            'Expediente actualizado correctamente.',
            'nutriologo.pacientes.show',
            ['paciente' => $id->id],
            ['id' => $id->id],
        );
    }

    /**
     * Si el expediente acaba de vincularse a un niño, las mediciones y planes
     * ya registrados también deben pasar a ser visibles desde la app móvil.
     */
    private function propagarVinculoAlHistorico(Paciente $paciente): void
    {
        if ($paciente->nino_id === null) {
            return;
        }

        $paciente->evaluaciones()->whereNull('nino_id')->update(['nino_id' => $paciente->nino_id]);
        $paciente->menus()->whereNull('nino_id')->update(['nino_id' => $paciente->nino_id]);
    }
}
