@extends('layouts.app')

@section('title', 'Estadísticas - Administrador')

@section('page-title', 'Estadísticas del sistema')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <x-page-header title="Estadísticas operativas" subtitle="Indicadores agregados con filtros por periodo, rol y estado." />

    {{-- Panel de filtros --}}
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-slate-200/80 dark:border-slate-800 p-5 mb-6">
        <form method="GET" action="{{ route('admin.estadisticas.index') }}" id="statsFilterForm" class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="fas fa-filter text-emerald-600"></i>
                        Filtros de análisis
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Periodo activo: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $periodoLabel }}</span>
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.estadisticas.index') }}"
                       class="px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                        Restablecer
                    </a>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                        Aplicar filtros
                    </button>
                </div>
            </div>

            {{-- Presets de periodo --}}
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-2">Periodo</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($filtros['periodos'] as $key => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="periodo" value="{{ $key }}" class="peer sr-only"
                                   @checked($filtros['periodo'] === $key)>
                            <span class="inline-block px-3 py-1.5 text-sm rounded-full border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 transition">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                {{-- Rango personalizado --}}
                <div id="customDateFields" class="{{ $filtros['periodo'] === 'custom' ? '' : 'hidden' }} lg:col-span-2">
                    <label for="desde" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Desde</label>
                    <input type="date" name="desde" id="desde" value="{{ $filtros['desde'] }}"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3 py-2">
                </div>
                <div id="customDateFieldsHasta" class="{{ $filtros['periodo'] === 'custom' ? '' : 'hidden' }} lg:col-span-2">
                    <label for="hasta" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Hasta</label>
                    <input type="date" name="hasta" id="hasta" value="{{ $filtros['hasta'] }}"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3 py-2">
                </div>

                {{-- Meses en gráfica (todo el historial) --}}
                <div id="mesesGraficaField" class="{{ $filtros['periodo'] === 'todo' ? '' : 'hidden' }}">
                    <label for="meses_grafica" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Meses en gráfica</label>
                    <select name="meses_grafica" id="meses_grafica"
                            class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3 py-2">
                        @foreach([3, 6, 12] as $m)
                            <option value="{{ $m }}" @selected($filtros['meses_grafica'] === $m)>{{ $m }} meses</option>
                        @endforeach
                    </select>
                </div>

                {{-- Rol --}}
                <div>
                    <label for="rol" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Rol de usuario</label>
                    <select name="rol" id="rol"
                            class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3 py-2">
                        <option value="">Todos los roles</option>
                        <option value="admin" @selected($filtros['rol'] === 'admin')>Administradores</option>
                        <option value="nutriologo" @selected($filtros['rol'] === 'nutriologo')>Nutriólogos</option>
                        <option value="padre" @selected($filtros['rol'] === 'padre')>Padres</option>
                    </select>
                </div>

                {{-- Estado cita --}}
                <div>
                    <label for="estado_cita" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Estado de citas</label>
                    <select name="estado_cita" id="estado_cita"
                            class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3 py-2">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" @selected($filtros['estado_cita'] === 'pendiente')>Pendiente</option>
                        <option value="asignada" @selected($filtros['estado_cita'] === 'asignada')>Asignada</option>
                        <option value="confirmada" @selected($filtros['estado_cita'] === 'confirmada')>Confirmada</option>
                        <option value="cancelada" @selected($filtros['estado_cita'] === 'cancelada')>Cancelada</option>
                    </select>
                </div>

                {{-- Nutriólogo --}}
                <div class="lg:col-span-2">
                    <label for="nutriologo_id" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Nutriólogo (citas)</label>
                    <select name="nutriologo_id" id="nutriologo_id"
                            class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm px-3 py-2">
                        <option value="">Todos los nutriólogos</option>
                        @foreach($nutriologos as $nutriologo)
                            <option value="{{ $nutriologo->id_usuario }}" @selected($filtros['nutriologo_id'] === $nutriologo->id_usuario)>
                                {{ trim($nutriologo->nombre.' '.$nutriologo->apellido_paterno) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>

    {{-- KPIs principales --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <x-stat-card label="Usuarios" :value="$totales['usuarios']" icon="fa-users" color="sky" :hint="$periodoLabel" />
        <x-stat-card label="Niños (app)" :value="$totales['ninos']" icon="fa-child" color="emerald" :hint="$periodoLabel" />
        <x-stat-card label="Pacientes" :value="$totales['pacientes']" icon="fa-hospital-user" color="emerald" :hint="$periodoLabel" />
        <x-stat-card label="Evaluaciones" :value="$totales['evaluaciones']" icon="fa-clipboard-check" color="amber" :hint="$periodoLabel" />
        <x-stat-card label="Citas" :value="$totales['citas']" icon="fa-calendar-check" color="violet" :hint="$periodoLabel" />
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <x-stat-card label="Menús" :value="$totales['menus']" icon="fa-utensils" color="amber" :hint="$periodoLabel" />
        <x-stat-card label="Comentarios" :value="$totales['comentarios']" icon="fa-comments" color="sky" :hint="$periodoLabel" />
        <x-stat-card label="Discusiones" :value="$totales['discusiones']" icon="fa-comments" color="violet" :hint="$periodoLabel" />
        <x-stat-card label="Contactos web" :value="$totales['contactos']" icon="fa-envelope" color="rose" :hint="$periodoLabel" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100 mb-1">Usuarios por rol</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Registros en el periodo seleccionado</p>
            <canvas id="rolesChart" height="200"></canvas>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100 mb-1">Citas por estado</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Distribución según filtros activos</p>
            @if(count($citasPorEstado))
                <canvas id="citasChart" height="200"></canvas>
            @else
                <x-empty-state icon="fa-calendar" title="Sin citas en este periodo" />
            @endif
        </div>
    </div>

    {{-- Gráfica temporal con toggles de series --}}
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100">Actividad en el tiempo</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Evolución por {{ $serieTemporal->count() > 0 && str_contains($serieTemporal->first()['label'] ?? '', ' ') && strlen($serieTemporal->first()['label'] ?? '') <= 6 ? 'día' : 'mes' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3" id="seriesToggles">
                @php
                    $series = [
                        'usuarios' => ['label' => 'Usuarios', 'color' => '#0ea5e9', 'default' => true],
                        'evaluaciones' => ['label' => 'Evaluaciones', 'color' => '#10b981', 'default' => true],
                        'citas' => ['label' => 'Citas', 'color' => '#a855f7', 'default' => true],
                        'menus' => ['label' => 'Menús', 'color' => '#f59e0b', 'default' => false],
                        'comunidad' => ['label' => 'Comunidad', 'color' => '#f43f5e', 'default' => false],
                    ];
                @endphp
                @foreach($series as $key => $serie)
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer select-none">
                        <input type="checkbox" class="series-toggle rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                               data-series="{{ $key }}" @checked($serie['default'])>
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $serie['color'] }}"></span>
                        {{ $serie['label'] }}
                    </label>
                @endforeach
            </div>
        </div>
        <canvas id="mensualChart" height="120"></canvas>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    var roles = @json($usuariosPorRol);
    var serie = @json($serieTemporal);
    var citasEstado = @json($citasPorEstado);
    var seriesMeta = @json($series);

    var isDark = document.documentElement.classList.contains('dark');
    var gridColor = isDark ? 'rgba(148, 163, 184, 0.2)' : 'rgba(148, 163, 184, 0.35)';
    var textColor = isDark ? '#cbd5e1' : '#475569';

    Chart.defaults.color = textColor;
    Chart.defaults.borderColor = gridColor;

    new Chart(document.getElementById('rolesChart'), {
        type: 'doughnut',
        data: {
            labels: ['Administradores', 'Nutriólogos', 'Padres'],
            datasets: [{
                data: [roles.admin, roles.nutriologo, roles.padre],
                backgroundColor: ['#8b5cf6', '#10b981', '#f59e0b'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                            var pct = total ? Math.round((ctx.raw / total) * 100) : 0;
                            return ctx.label + ': ' + ctx.raw + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });

    if (Object.keys(citasEstado).length) {
        var estadoLabels = Object.keys(citasEstado).map(function (e) {
            return e.charAt(0).toUpperCase() + e.slice(1).replace(/_/g, ' ');
        });
        new Chart(document.getElementById('citasChart'), {
            type: 'bar',
            data: {
                labels: estadoLabels,
                datasets: [{
                    label: 'Citas',
                    data: Object.values(citasEstado),
                    backgroundColor: ['#f59e0b', '#0ea5e9', '#10b981', '#ef4444'],
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: gridColor } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    var labels = serie.map(function (m) { return m.label; });
    var datasetMap = {};
    Object.keys(seriesMeta).forEach(function (key) {
        datasetMap[key] = {
            label: seriesMeta[key].label,
            data: serie.map(function (m) { return m[key] || 0; }),
            backgroundColor: seriesMeta[key].color,
            borderColor: seriesMeta[key].color,
            hidden: !seriesMeta[key].default
        };
    });

    var mensualChart = new Chart(document.getElementById('mensualChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: Object.keys(seriesMeta).map(function (key) {
                var meta = seriesMeta[key];
                return {
                    label: meta.label,
                    data: serie.map(function (m) { return m[key] || 0; }),
                    backgroundColor: meta.color,
                    borderRadius: 4,
                    hidden: !meta.default
                };
            })
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: gridColor } }
            }
        }
    });

    document.querySelectorAll('.series-toggle').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            var key = checkbox.getAttribute('data-series');
            var idx = Object.keys(seriesMeta).indexOf(key);
            if (idx >= 0) {
                mensualChart.setDatasetVisibility(idx, checkbox.checked);
                mensualChart.update();
            }
        });
    });

    // UI filtros: mostrar campos según periodo
    var periodoInputs = document.querySelectorAll('input[name="periodo"]');
    var customFields = document.getElementById('customDateFields');
    var customFieldsHasta = document.getElementById('customDateFieldsHasta');
    var mesesField = document.getElementById('mesesGraficaField');

    function syncPeriodoFields() {
        var selected = document.querySelector('input[name="periodo"]:checked');
        var val = selected ? selected.value : '6m';
        var isCustom = val === 'custom';
        var isTodo = val === 'todo';
        customFields.classList.toggle('hidden', !isCustom);
        customFieldsHasta.classList.toggle('hidden', !isCustom);
        mesesField.classList.toggle('hidden', !isTodo);
    }

    periodoInputs.forEach(function (input) {
        input.addEventListener('change', syncPeriodoFields);
    });
    syncPeriodoFields();
})();
</script>
@endpush
