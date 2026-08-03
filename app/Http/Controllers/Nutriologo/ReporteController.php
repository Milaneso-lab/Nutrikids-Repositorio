<?php

namespace App\Http\Controllers\Nutriologo;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion;
use App\Models\Paciente;
use App\Models\Reporte;
use App\Services\Nutricion\AntropometriaService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    public function __construct(private AntropometriaService $antropometria)
    {
    }

    public function index()
    {
        $reportes = Reporte::with('paciente')
            ->latest()
            ->get();

        $pacientes = Paciente::withCount('evaluaciones')
            ->has('evaluaciones')
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get();

        $statsMeses = collect(range(5, 0))->map(function ($monthsAgo) use ($reportes) {
            $key = now()->copy()->startOfMonth()->subMonths($monthsAgo)->format('Y-m');
            $label = now()->copy()->startOfMonth()->subMonths($monthsAgo)->translatedFormat('M Y');

            return [
                'label' => $label,
                'total' => $reportes->filter(fn ($r) => optional($r->created_at)?->format('Y-m') === $key)->count(),
            ];
        });

        $statsJson = json_encode([
            'labels' => $statsMeses->pluck('label'),
            'totales' => $statsMeses->pluck('total'),
        ], JSON_UNESCAPED_UNICODE);

        return view('nutriologo.reportes.index', compact('reportes', 'pacientes', 'statsJson'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'titulo' => 'nullable|string|max:150',
        ]);

        $paciente = Paciente::with(['evaluaciones' => function ($query) {
            $query->orderByDesc('created_at');
        }])->findOrFail($validated['paciente_id']);

        if ($paciente->evaluaciones->isEmpty()) {
            return redirect()
                ->route('nutriologo.reportes.index')
                ->with('error', 'No se puede generar el reporte porque el paciente no tiene evaluaciones registradas.');
        }

        $reportData = $this->buildReportData($paciente, $paciente->evaluaciones);

        $reporte = Reporte::create([
            'paciente_id' => $paciente->id,
            'titulo' => $validated['titulo'] ?: 'Reporte de progreso - '.$this->patientName($paciente),
            'contenido' => json_encode($reportData, JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()
            ->route('nutriologo.reportes.show', $reporte)
            ->with('success', 'Reporte generado correctamente.');
    }

    public function show(Reporte $reporte)
    {
        $reporte->load('paciente');

        abort_if(!$reporte->paciente, 404, 'El reporte no tiene un paciente asociado.');

        $evaluaciones = Evaluacion::where('paciente_id', $reporte->paciente_id)
            ->orderByDesc('created_at')
            ->get();

        $reportData = $this->buildReportData($reporte->paciente, $evaluaciones);

        return view('nutriologo.reportes.show', compact('reporte', 'evaluaciones', 'reportData'));
    }

    public function exportPdf(Reporte $reporte)
    {
        $reporte->load('paciente');

        abort_if(!$reporte->paciente, 404, 'El reporte no tiene un paciente asociado.');

        $evaluaciones = Evaluacion::where('paciente_id', $reporte->paciente_id)
            ->orderByDesc('created_at')
            ->get();

        $reportData = $this->buildReportData($reporte->paciente, $evaluaciones);

        $pdf = Pdf::loadView('nutriologo.reportes.pdf', compact('reporte', 'evaluaciones', 'reportData'))
            ->setPaper('A4');

        $filename = 'reporte-'.$reporte->id.'-paciente-'.$reporte->paciente_id.'.pdf';

        return $pdf->download($filename);
    }

    private function buildReportData(Paciente $paciente, $evaluaciones): array
    {
        $formattedEvaluaciones = $evaluaciones->map(function (Evaluacion $evaluacion) {
            $peso = $this->antropometria->normalizeDecimal($evaluacion->peso);
            $talla = $this->antropometria->normalizeDecimal($evaluacion->talla);

            return [
                'fecha' => optional($evaluacion->created_at)->format('d/m/Y H:i'),
                'peso' => $evaluacion->peso,
                'talla' => $evaluacion->talla,
                'recomendaciones' => $evaluacion->recomendaciones,
                'imc' => $this->antropometria->calculateImc($peso, $talla),
            ];
        });

        $imcs = $formattedEvaluaciones
            ->pluck('imc')
            ->filter(fn ($value) => $value !== null);

        $ultimaEvaluacion = $formattedEvaluaciones->first();

        return [
            'paciente_nombre' => $this->patientName($paciente),
            'periodo' => $evaluaciones->isNotEmpty()
                ? optional($evaluaciones->last()->created_at)->format('d/m/Y').' - '.optional($evaluaciones->first()->created_at)->format('d/m/Y')
                : 'Sin evaluaciones',
            'imc_promedio' => $imcs->isNotEmpty() ? round($imcs->avg(), 2) : null,
            'total_evaluaciones' => $evaluaciones->count(),
            'ultima_evaluacion' => $ultimaEvaluacion['fecha'] ?? null,
            'evaluaciones' => $formattedEvaluaciones->all(),
        ];
    }

    private function patientName(Paciente $paciente): string
    {
        return trim(($paciente->nombre ?? '').' '.($paciente->apellidos ?? ''));
    }
}
