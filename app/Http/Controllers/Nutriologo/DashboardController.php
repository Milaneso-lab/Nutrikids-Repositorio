<?php

namespace App\Http\Controllers\Nutriologo;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Evaluacion;
use App\Models\Menu;
use App\Models\Paciente;
use App\Models\Reporte;
use App\Services\Nutricion\AntropometriaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __construct(private AntropometriaService $antropometria)
    {
    }

    public function index()
    {
        $uid = Auth::id();

        $totalCitasNutri = Cita::where('id_nutriologo', $uid)
            ->where('estado', '!=', Cita::ESTADO_CANCELADA)
            ->count();

        $consultasHoy = Cita::with('padre')
            ->where('id_nutriologo', $uid)
            ->whereDate('fecha_preferida', today())
            ->whereIn('estado', [Cita::ESTADO_ASIGNADA, Cita::ESTADO_CONFIRMADA])
            ->orderBy('franja')
            ->get();

        $pendientesDisponibles = Cita::where('estado', Cita::ESTADO_PENDIENTE)
            ->whereNull('id_nutriologo')
            ->count();

        $misCitasDashboard = Cita::with('padre')
            ->where('id_nutriologo', $uid)
            ->where('fecha_preferida', '>=', today())
            ->where('estado', '!=', Cita::ESTADO_CANCELADA)
            ->orderBy('fecha_preferida')
            ->orderBy('franja')
            ->take(5)
            ->get();

        $totalPacientes = Paciente::count();
        $pacientesActivos = Schema::hasColumn('pacientes', 'estado_paciente')
            ? Paciente::where('estado_paciente', Paciente::ESTADO_ACTIVO)->count()
            : $totalPacientes;

        $imcs = Evaluacion::query()
            ->with('paciente')
            ->get()
            ->map(function (Evaluacion $evaluacion) {
                $peso = $this->antropometria->normalizeDecimal($evaluacion->peso);
                $talla = $this->antropometria->normalizeDecimal($evaluacion->talla);

                return [
                    'evaluacion' => $evaluacion,
                    'imc' => $this->antropometria->calculateImc($peso, $talla),
                ];
            })
            ->filter(fn ($row) => $row['imc'] !== null)
            ->values();

        $imcPromedio = $imcs->isNotEmpty() ? round($imcs->avg('imc'), 2) : null;
        $alertasPendientes = $imcs->filter(fn ($row) => $row['imc'] < 18.5 || $row['imc'] >= 25)->count();

        $alertas = collect();

        $alertas = $alertas->merge(
            $imcs
                ->filter(fn ($row) => $row['imc'] < 18.5 || $row['imc'] >= 25)
                ->sortByDesc(fn ($row) => optional($row['evaluacion']->created_at)->timestamp ?? 0)
                ->take(3)
                ->map(function ($row) {
                    $paciente = $row['evaluacion']->paciente;
                    $nombrePaciente = $paciente ? trim(($paciente->nombre ?? '').' '.($paciente->apellidos ?? '')) : 'Paciente sin nombre';

                    return [
                        'tipo' => $row['imc'] < 18.5 ? 'danger' : 'warning',
                        'titulo' => $row['imc'] < 18.5 ? 'IMC bajo detectado' : 'IMC alto detectado',
                        'descripcion' => 'Paciente: '.$nombrePaciente.' - IMC: '.number_format($row['imc'], 2),
                        'fecha' => optional($row['evaluacion']->created_at)?->diffForHumans() ?? 'Sin fecha',
                        'sort_key' => optional($row['evaluacion']->created_at)?->timestamp ?? 0,
                    ];
                })
        );

        $pacientesRecientes = Paciente::latest()
            ->take(3)
            ->get()
            ->map(function (Paciente $paciente) {
                return [
                    'tipo' => 'info',
                    'titulo' => 'Paciente registrado recientemente',
                    'descripcion' => 'Paciente: '.trim(($paciente->nombre ?? '').' '.($paciente->apellidos ?? '')),
                    'fecha' => optional($paciente->created_at)?->diffForHumans() ?? 'Sin fecha',
                    'sort_key' => optional($paciente->created_at)?->timestamp ?? 0,
                ];
            });

        $alertas = $alertas
            ->merge($pacientesRecientes)
            ->sortByDesc('sort_key')
            ->take(5)
            ->values()
            ->map(function ($alerta) {
                unset($alerta['sort_key']);

                return $alerta;
            });

        $chartMonths = collect(range(5, 0))
            ->map(function ($monthsAgo) {
                $month = now()->copy()->startOfMonth()->subMonths($monthsAgo);

                return [
                    'key' => $month->format('Y-m'),
                    'label' => $month->translatedFormat('M Y'),
                ];
            })
            ->values();

        $imcHistory = $chartMonths->map(function ($month) use ($imcs) {
            $monthImcs = $imcs
                ->filter(function ($row) use ($month) {
                    return optional($row['evaluacion']->created_at)?->format('Y-m') === $month['key'];
                })
                ->pluck('imc');

            return $monthImcs->isNotEmpty() ? round($monthImcs->avg(), 2) : null;
        });

        $chartLabels = $chartMonths->pluck('label');

        $promedioSerie = [
            'labels' => $chartLabels->values()->all(),
            'data' => $imcHistory->values()->all(),
            'pesos' => [],
            'tallas_cm' => [],
            'nombre' => 'Promedio mensual',
        ];

        $evaluacionesPorPaciente = Evaluacion::query()
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy('paciente_id');

        $pacientesParaGrafica = [];
        $imcChartData = ['__promedio__' => $promedioSerie];

        foreach (Paciente::query()->orderBy('apellidos')->orderBy('nombre')->get() as $paciente) {
            $nombrePac = trim(($paciente->nombre ?? '').' '.($paciente->apellidos ?? '')) ?: 'Paciente #'.$paciente->id;
            $pacientesParaGrafica[] = [
                'id' => $paciente->id,
                'nombre' => $nombrePac,
            ];

            $labelsEv = [];
            $dataEv = [];
            $pesosEv = [];
            $tallasCmEv = [];
            foreach ($evaluacionesPorPaciente->get($paciente->id, collect()) as $ev) {
                $peso = $this->antropometria->normalizeDecimal($ev->peso);
                $talla = $this->antropometria->normalizeDecimal($ev->talla);
                $imcEv = $this->antropometria->calculateImc($peso, $talla);
                if ($imcEv === null) {
                    continue;
                }
                $labelsEv[] = optional($ev->created_at)->format('d/m/Y') ?? '—';
                $dataEv[] = $imcEv;
                $pesosEv[] = round($peso, 2);
                $tallaCm = $talla !== null ? ($talla > 3 ? $talla : $talla * 100) : 0;
                $tallasCmEv[] = round($tallaCm, 1);
            }

            $imcChartData[(string) $paciente->id] = [
                'labels' => $labelsEv,
                'data' => $dataEv,
                'pesos' => $pesosEv,
                'tallas_cm' => $tallasCmEv,
                'nombre' => $nombrePac,
            ];
        }

        $imcChartDataJson = json_encode($imcChartData, JSON_UNESCAPED_UNICODE);

        $totalMenus = Menu::count();
        $totalReportes = Reporte::count();
        $pacientesConEvaluacion = Evaluacion::distinct()->count('paciente_id');
        $cumplimientoPlanes = $totalPacientes > 0
            ? (int) round(min(100, ($totalMenus / $totalPacientes) * 100))
            : 0;
        $ultimasRecomendaciones = Evaluacion::query()
            ->whereNotNull('recomendaciones')
            ->where('recomendaciones', '!=', '')
            ->with('paciente')
            ->latest()
            ->take(5)
            ->get();

        $actividadReciente = collect()
            ->merge(Evaluacion::with('paciente')->latest()->take(4)->get()->map(fn ($e) => [
                'tipo' => 'evaluacion',
                'icon' => 'fa-clipboard-check',
                'titulo' => 'Evaluación registrada',
                'descripcion' => trim(($e->paciente->nombre ?? '').' '.($e->paciente->apellidos ?? '')),
                'fecha' => $e->created_at,
            ]))
            ->merge(Menu::with('paciente')->latest()->take(4)->get()->map(fn ($m) => [
                'tipo' => 'menu',
                'icon' => 'fa-utensils',
                'titulo' => 'Plan alimenticio: '.$m->nombre,
                'descripcion' => trim(($m->paciente->nombre ?? '').' '.($m->paciente->apellidos ?? '')),
                'fecha' => $m->created_at,
            ]))
            ->merge(Reporte::with('paciente')->latest()->take(4)->get()->map(fn ($r) => [
                'tipo' => 'reporte',
                'icon' => 'fa-file-medical',
                'titulo' => $r->titulo ?: 'Reporte generado',
                'descripcion' => trim(($r->paciente->nombre ?? '').' '.($r->paciente->apellidos ?? '')),
                'fecha' => $r->created_at,
            ]))
            ->sortByDesc('fecha')
            ->take(8)
            ->values();

        return view('nutriologo.dashboard', compact(
            'totalPacientes',
            'pacientesActivos',
            'totalCitasNutri',
            'consultasHoy',
            'imcPromedio',
            'alertasPendientes',
            'alertas',
            'chartLabels',
            'imcHistory',
            'pendientesDisponibles',
            'misCitasDashboard',
            'pacientesParaGrafica',
            'imcChartDataJson',
            'totalMenus',
            'totalReportes',
            'pacientesConEvaluacion',
            'cumplimientoPlanes',
            'ultimasRecomendaciones',
            'actividadReciente'
        ));
    }
}
