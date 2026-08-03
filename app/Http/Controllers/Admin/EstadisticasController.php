<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Comentario;
use App\Models\Contacto;
use App\Models\Discusion;
use App\Models\Evaluacion;
use App\Models\Menu;
use App\Models\Nino;
use App\Models\Paciente;
use App\Models\Reporte;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EstadisticasController extends Controller
{
    /** @var array<string, string|null> */
    private const COLUMNAS_FECHA = [
        'contactos' => 'fecha_creacion',
        'comentarios' => 'fecha_comentario',
        'discusiones' => 'fecha_creacion',
        'usuarios' => null,
        'evaluaciones' => 'fecha_evaluacion',
        'citas' => 'created_at',
        'menus' => 'created_at',
        'reportes' => 'created_at',
        'pacientes' => 'created_at',
        'ninos' => 'created_at',
    ];
    /** @var array<string, string> */
    private const PERIODOS = [
        '7d' => 'Últimos 7 días',
        '30d' => 'Últimos 30 días',
        '3m' => 'Últimos 3 meses',
        '6m' => 'Últimos 6 meses',
        '12m' => 'Últimos 12 meses',
        'todo' => 'Todo el historial',
        'custom' => 'Personalizado',
    ];

    public function index(Request $request)
    {
        $filtros = $this->normalizarFiltros($request);
        [$desde, $hasta, $granularidad] = $this->resolverRango($filtros);

        $totales = [
            'usuarios' => $this->contarUsuarios($desde, $hasta, $filtros['rol']),
            'ninos' => $this->contarModelo(Nino::class, $desde, $hasta),
            'pacientes' => $this->contarModelo(Paciente::class, $desde, $hasta),
            'evaluaciones' => $this->contarEvaluaciones($desde, $hasta),
            'menus' => $this->contarModelo(Menu::class, $desde, $hasta),
            'reportes' => $this->contarModelo(Reporte::class, $desde, $hasta),
            'citas' => $this->contarCitas($desde, $hasta, $filtros['estado_cita'], $filtros['nutriologo_id']),
            'contactos' => $this->contarModelo(Contacto::class, $desde, $hasta),
            'comentarios' => $this->contarModelo(Comentario::class, $desde, $hasta),
            'discusiones' => $this->contarModelo(Discusion::class, $desde, $hasta),
        ];

        $usuariosPorRol = $this->usuariosPorRol($desde, $hasta, $filtros['rol']);

        $citasPorEstado = $this->citasPorEstado(
            $desde,
            $hasta,
            $filtros['estado_cita'],
            $filtros['nutriologo_id']
        );

        $serieTemporal = $this->serieTemporal($desde, $hasta, $granularidad, $filtros);

        $nutriologos = User::query()
            ->where('rol', 'nutriologo')
            ->orderBy('nombre')
            ->get(['id_usuario', 'nombre', 'apellido_paterno']);

        $periodoLabel = $this->etiquetaPeriodo($filtros, $desde, $hasta);

        return view('admin.estadisticas.index', compact(
            'totales',
            'usuariosPorRol',
            'citasPorEstado',
            'serieTemporal',
            'filtros',
            'nutriologos',
            'periodoLabel',
        ));
    }

    /** @return array<string, mixed> */
    private function normalizarFiltros(Request $request): array
    {
        $periodo = $request->input('periodo', '6m');
        if (! array_key_exists($periodo, self::PERIODOS)) {
            $periodo = '6m';
        }

        $mesesGrafica = (int) $request->input('meses_grafica', 6);
        if (! in_array($mesesGrafica, [3, 6, 12], true)) {
            $mesesGrafica = 6;
        }

        $rol = $request->input('rol', '');
        if (! in_array($rol, ['', 'admin', 'nutriologo', 'padre'], true)) {
            $rol = '';
        }

        $estadoCita = $request->input('estado_cita', '');
        $estadosValidos = ['', Cita::ESTADO_PENDIENTE, Cita::ESTADO_ASIGNADA, Cita::ESTADO_CONFIRMADA, Cita::ESTADO_CANCELADA];
        if (! in_array($estadoCita, $estadosValidos, true)) {
            $estadoCita = '';
        }

        $nutriologoId = $request->filled('nutriologo_id') ? (int) $request->input('nutriologo_id') : null;

        return [
            'periodo' => $periodo,
            'desde' => $request->input('desde'),
            'hasta' => $request->input('hasta'),
            'meses_grafica' => $mesesGrafica,
            'rol' => $rol,
            'estado_cita' => $estadoCita,
            'nutriologo_id' => $nutriologoId,
            'periodos' => self::PERIODOS,
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{0: ?Carbon, 1: ?Carbon, 2: string}
     */
    private function resolverRango(array $filtros): array
    {
        $now = now()->endOfDay();

        if ($filtros['periodo'] === 'todo') {
            return [null, null, 'month'];
        }

        if ($filtros['periodo'] === 'custom') {
            $desde = $filtros['desde'] ? Carbon::parse($filtros['desde'])->startOfDay() : now()->subMonths(5)->startOfMonth();
            $hasta = $filtros['hasta'] ? Carbon::parse($filtros['hasta'])->endOfDay() : $now;
            if ($desde->gt($hasta)) {
                [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
            }

            $dias = $desde->diffInDays($hasta) + 1;
            $granularidad = $dias <= 31 ? 'day' : 'month';

            return [$desde, $hasta, $granularidad];
        }

        $desde = match ($filtros['periodo']) {
            '7d' => now()->subDays(6)->startOfDay(),
            '30d' => now()->subDays(29)->startOfDay(),
            '3m' => now()->subMonths(2)->startOfMonth(),
            '12m' => now()->subMonths(11)->startOfMonth(),
            default => now()->subMonths(5)->startOfMonth(),
        };

        $granularidad = in_array($filtros['periodo'], ['7d', '30d'], true) ? 'day' : 'month';

        return [$desde, $now, $granularidad];
    }

    private function contarUsuarios(?Carbon $desde, ?Carbon $hasta, string $rol): int
    {
        $query = User::query();
        $this->aplicarRangoModelo($query, $desde, $hasta);
        if ($rol !== '') {
            $query->where('rol', $rol);
        }

        return $query->count();
    }

    /** @param  class-string<Model>  $modelClass */
    private function contarModelo(string $modelClass, ?Carbon $desde, ?Carbon $hasta): int
    {
        $query = $modelClass::query();
        $this->aplicarRangoModelo($query, $desde, $hasta);

        return $query->count();
    }

    private function contarEvaluaciones(?Carbon $desde, ?Carbon $hasta): int
    {
        $query = Evaluacion::query();
        $this->aplicarRangoEvaluacion($query, $desde, $hasta);

        return $query->count();
    }

    private function contarCitas(?Carbon $desde, ?Carbon $hasta, string $estado, ?int $nutriologoId): int
    {
        $query = Cita::query();
        $this->aplicarRangoModelo($query, $desde, $hasta);
        if ($estado !== '') {
            $query->where('estado', $estado);
        }
        if ($nutriologoId) {
            $query->where('id_nutriologo', $nutriologoId);
        }

        return $query->count();
    }

    /** @return array<string, int> */
    private function usuariosPorRol(?Carbon $desde, ?Carbon $hasta, string $rolFiltro): array
    {
        $base = ['admin' => 0, 'nutriologo' => 0, 'padre' => 0];
        $roles = $rolFiltro !== '' ? [$rolFiltro] : array_keys($base);

        foreach ($roles as $rol) {
            $query = User::query()->where('rol', $rol);
            $this->aplicarRangoModelo($query, $desde, $hasta);
            $base[$rol] = $query->count();
        }

        return $base;
    }

    /** @return array<string, int> */
    private function citasPorEstado(?Carbon $desde, ?Carbon $hasta, string $estadoFiltro, ?int $nutriologoId): array
    {
        $query = Cita::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado');

        $this->aplicarRangoModelo($query, $desde, $hasta);

        if ($estadoFiltro !== '') {
            $query->where('estado', $estadoFiltro);
        }
        if ($nutriologoId) {
            $query->where('id_nutriologo', $nutriologoId);
        }

        return $query->pluck('total', 'estado')->map(fn ($v) => (int) $v)->all();
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return Collection<int, array<string, mixed>>
     */
    private function serieTemporal(?Carbon $desde, ?Carbon $hasta, string $granularidad, array $filtros): Collection
    {
        if ($filtros['periodo'] === 'todo') {
            $desde = now()->copy()->subMonths($filtros['meses_grafica'] - 1)->startOfMonth();
            $hasta = now()->endOfDay();
            $granularidad = 'month';
        }

        if (! $desde || ! $hasta) {
            $desde = now()->copy()->subMonths($filtros['meses_grafica'] - 1)->startOfMonth();
            $hasta = now()->endOfDay();
            $granularidad = 'month';
        }

        $buckets = $this->generarBuckets($desde, $hasta, $granularidad);

        return $buckets->map(function (array $bucket) use ($filtros) {
            [$inicio, $fin, $label] = $bucket;

            $usuarios = User::query();
            $this->aplicarRangoModelo($usuarios, $inicio, $fin);
            if ($filtros['rol'] !== '') {
                $usuarios->where('rol', $filtros['rol']);
            }

            $evaluaciones = Evaluacion::query();
            $this->aplicarRangoEvaluacion($evaluaciones, $inicio, $fin);

            $citas = Cita::query();
            $this->aplicarRangoModelo($citas, $inicio, $fin);
            if ($filtros['estado_cita'] !== '') {
                $citas->where('estado', $filtros['estado_cita']);
            }
            if ($filtros['nutriologo_id']) {
                $citas->where('id_nutriologo', $filtros['nutriologo_id']);
            }

            $menus = Menu::query();
            $this->aplicarRangoModelo($menus, $inicio, $fin);

            $totalComentarios = Comentario::query();
            $this->aplicarRangoModelo($totalComentarios, $inicio, $fin);

            $totalDiscusiones = Discusion::query();
            $this->aplicarRangoModelo($totalDiscusiones, $inicio, $fin);

            $totalContactos = Contacto::query();
            $this->aplicarRangoModelo($totalContactos, $inicio, $fin);

            $comentariosN = $totalComentarios->count();
            $discusionesN = $totalDiscusiones->count();
            $contactosN = $totalContactos->count();

            return [
                'label' => $label,
                'usuarios' => $usuarios->count(),
                'evaluaciones' => $evaluaciones->count(),
                'citas' => $citas->count(),
                'menus' => $menus->count(),
                'comentarios' => $comentariosN,
                'discusiones' => $discusionesN,
                'contactos' => $contactosN,
                'comunidad' => $comentariosN + $discusionesN + $contactosN,
            ];
        });
    }

    /**
     * @return Collection<int, array{0: Carbon, 1: Carbon, 2: string}>
     */
    private function generarBuckets(Carbon $desde, Carbon $hasta, string $granularidad): Collection
    {
        $buckets = collect();

        if ($granularidad === 'day') {
            $cursor = $desde->copy()->startOfDay();
            while ($cursor->lte($hasta)) {
                $fin = $cursor->copy()->endOfDay();
                if ($fin->gt($hasta)) {
                    $fin = $hasta->copy();
                }
                $buckets->push([
                    $cursor->copy(),
                    $fin,
                    $cursor->translatedFormat('d M'),
                ]);
                $cursor->addDay();
            }

            return $buckets;
        }

        $cursor = $desde->copy()->startOfMonth();
        while ($cursor->lte($hasta)) {
            $inicio = $cursor->copy()->startOfMonth();
            $fin = $cursor->copy()->endOfMonth();
            if ($fin->gt($hasta)) {
                $fin = $hasta->copy();
            }
            if ($inicio->lt($desde)) {
                $inicio = $desde->copy();
            }
            $buckets->push([
                $inicio,
                $fin,
                $cursor->translatedFormat('M Y'),
            ]);
            $cursor->addMonth();
        }

        return $buckets;
    }

    /** @param  Builder<Model>  $query */
    private function aplicarRangoModelo(Builder $query, ?Carbon $desde, ?Carbon $hasta): void
    {
        if (! $desde || ! $hasta) {
            return;
        }

        $model = $query->getModel();
        $columna = self::COLUMNAS_FECHA[$model->getTable()] ?? 'created_at';

        if ($columna === null) {
            return;
        }

        if ($model->getTable() === 'evaluaciones') {
            $this->aplicarRangoEvaluacion($query, $desde, $hasta);

            return;
        }

        $this->aplicarRango($query, $columna, $desde, $hasta);
    }

    /** @param  Builder<Model>  $query */
    private function aplicarRangoEvaluacion(Builder $query, ?Carbon $desde, ?Carbon $hasta): void
    {
        if (! $desde || ! $hasta) {
            return;
        }

        $query->where(function (Builder $q) use ($desde, $hasta) {
            $q->whereBetween('fecha_evaluacion', [$desde->toDateString(), $hasta->toDateString()])
                ->orWhere(function (Builder $inner) use ($desde, $hasta) {
                    $inner->whereNull('fecha_evaluacion')
                        ->whereBetween('created_at', [$desde, $hasta]);
                });
        });
    }

    /** @param  Builder<Model>  $query */
    private function aplicarRango(Builder $query, string $column, ?Carbon $desde, ?Carbon $hasta): void
    {
        if ($desde && $hasta) {
            $query->whereBetween($column, [$desde, $hasta]);
        }
    }

    /** @param  array<string, mixed>  $filtros */
    private function etiquetaPeriodo(array $filtros, ?Carbon $desde, ?Carbon $hasta): string
    {
        if ($filtros['periodo'] === 'todo') {
            return 'Todo el historial (gráfica: últimos '.$filtros['meses_grafica'].' meses)';
        }

        if ($filtros['periodo'] === 'custom' && $desde && $hasta) {
            return $desde->translatedFormat('d M Y').' – '.$hasta->translatedFormat('d M Y');
        }

        return self::PERIODOS[$filtros['periodo']] ?? 'Periodo seleccionado';
    }
}
