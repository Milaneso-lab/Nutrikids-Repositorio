@extends('layouts.app')

@section('title', 'Dashboard - Nutriólogo')

@section('page-title', 'Dashboard')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
    <!-- Métricas Rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border-l-4 border-green-500 dark:border-emerald-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-slate-400 text-sm">Pacientes Activos</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-slate-100 mt-2">{{ $pacientesActivos ?? $totalPacientes }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ $totalPacientes }} registrados</p>
                </div>
                <div class="bg-green-100 dark:bg-emerald-900/40 p-4 rounded-full">
                    <i class="fas fa-child text-green-600 dark:text-emerald-300 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border-l-4 border-blue-500 dark:border-sky-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-slate-400 text-sm">Mis citas activas</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-slate-100 mt-2">{{ $totalCitasNutri }}</p>
                    @if(($pendientesDisponibles ?? 0) > 0)
                        <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">{{ $pendientesDisponibles }} sin asignar · <a href="{{ route('nutriologo.citas.index') }}" class="underline font-medium hover:text-amber-900 dark:hover:text-amber-200">Ver bandeja</a></p>
                    @endif
                </div>
                <div class="bg-blue-100 dark:bg-sky-900/40 p-4 rounded-full">
                    <i class="fas fa-calendar-check text-blue-600 dark:text-sky-300 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border-l-4 border-yellow-500 dark:border-amber-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-slate-400 text-sm">IMC Promedio</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-slate-100 mt-2">{{ $imcPromedio !== null ? number_format($imcPromedio, 2) : 'Sin datos' }}</p>
                </div>
                <div class="bg-yellow-100 dark:bg-amber-900/40 p-4 rounded-full">
                    <i class="fas fa-weight text-yellow-600 dark:text-amber-300 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border-l-4 border-red-500 dark:border-rose-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-slate-400 text-sm">Alertas Pendientes</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-slate-100 mt-2">{{ $alertasPendientes }}</p>
                </div>
                <div class="bg-red-100 dark:bg-rose-900/40 p-4 rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-600 dark:text-rose-300 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    @if(isset($consultasHoy) && $consultasHoy->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 mb-8 border border-slate-200/80 dark:border-slate-800">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-100"><i class="fas fa-sun text-amber-500 mr-2"></i>Consultas de hoy</h3>
            <a href="{{ route('nutriologo.citas.agenda') }}" class="text-sm text-emerald-600 hover:underline">Ver agenda</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($consultasHoy as $cita)
                <div class="p-4 rounded-lg bg-amber-50 dark:bg-amber-950/25 border border-amber-200 dark:border-amber-900/50">
                    <p class="font-medium">{{ $cita->padre?->nombre }} {{ $cita->padre?->apellido_paterno }}</p>
                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $cita->franja === 'tarde' ? 'Tarde' : 'Mañana' }}</p>
                    @include('admin.citas._estado-badge', ['estado' => $cita->estado])
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Seguimiento de cumplimiento --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-stat-card label="Menús asignados" :value="$totalMenus" icon="fa-utensils" color="emerald" :href="route('nutriologo.menus.index')" />
        <x-stat-card label="Reportes generados" :value="$totalReportes" icon="fa-file-medical" color="sky" :href="route('nutriologo.reportes.index')" />
        <x-stat-card label="Cobertura de planes" :value="$cumplimientoPlanes.'%'" icon="fa-chart-line" color="amber" :hint="$pacientesConEvaluacion.' pacientes con evaluación'" />
    </div>

    @if(isset($ultimasRecomendaciones) && $ultimasRecomendaciones->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 mb-8 border border-slate-200/80 dark:border-slate-800">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-100 mb-4">Recomendaciones recientes</h3>
        <div class="space-y-3">
            @foreach($ultimasRecomendaciones as $rec)
                <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700">
                    <p class="text-sm font-semibold text-gray-800 dark:text-slate-100">
                        {{ trim(($rec->paciente->nombre ?? '').' '.($rec->paciente->apellidos ?? '')) ?: 'Paciente #'.$rec->paciente_id }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-slate-300 mt-1">{{ \Illuminate\Support\Str::limit($rec->recomendaciones, 160) }}</p>
                    <p class="text-xs text-gray-500 dark:text-slate-500 mt-2">{{ optional($rec->created_at)->diffForHumans() }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(isset($actividadReciente) && $actividadReciente->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 mb-8 border border-slate-200/80 dark:border-slate-800">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-100 mb-4">Actividad reciente</h3>
        <div class="space-y-3">
            @foreach($actividadReciente as $act)
                <div class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/60">
                    <i class="fas {{ $act['icon'] }} text-emerald-600 mt-1"></i>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium">{{ $act['titulo'] }}</p>
                        <p class="text-xs text-slate-500">{{ $act['descripcion'] }} · {{ optional($act['fecha'])->diffForHumans() }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Próximas Citas -->
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-100">Mis próximas citas</h3>
                <a href="{{ route('nutriologo.citas.index') }}" class="text-green-600 dark:text-emerald-400 hover:text-green-700 dark:hover:text-emerald-300 text-sm font-medium">Bandeja completa</a>
            </div>
            <div class="space-y-4">
                @forelse($misCitasDashboard as $cita)
                <div class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-slate-800/80 rounded-lg border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition">
                    <div class="w-12 h-12 bg-green-100 dark:bg-emerald-900/50 rounded-full flex items-center justify-center shrink-0">
                        <i class="fas fa-user text-green-600 dark:text-emerald-300"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 dark:text-slate-100 truncate">
                            {{ $cita->padre?->nombre }} {{ $cita->padre?->apellido_paterno }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-slate-400">
                            {{ $cita->fecha_preferida?->format('d/m/Y') }}, {{ $cita->franja === 'tarde' ? 'Tarde' : 'Mañana' }}
                        </p>
                    </div>
                    @include('admin.citas._estado-badge', ['estado' => $cita->estado])
                </div>
                @empty
                <p class="text-gray-500 dark:text-slate-400 text-sm text-center py-4">No tienes citas asignadas aún.</p>
                @endforelse
            </div>
        </div>

        <!-- Alertas y Notificaciones -->
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-100">Alertas</h3>
                <span class="px-3 py-1 bg-red-100 dark:bg-rose-900/40 text-red-700 dark:text-rose-200 rounded-full text-xs font-medium">{{ $alertas->count() }} activas</span>
            </div>
            <div class="space-y-4">
                @forelse($alertas as $alerta)
                    @php
                        $alertStyles = match($alerta['tipo']) {
                            'danger' => ['bg' => 'bg-red-50 dark:bg-red-950/35', 'border' => 'border-red-500 dark:border-red-700', 'icon' => 'fa-exclamation-circle', 'iconColor' => 'text-red-600 dark:text-red-400'],
                            'warning' => ['bg' => 'bg-yellow-50 dark:bg-amber-950/30', 'border' => 'border-yellow-500 dark:border-amber-600', 'icon' => 'fa-triangle-exclamation', 'iconColor' => 'text-yellow-600 dark:text-amber-400'],
                            default => ['bg' => 'bg-blue-50 dark:bg-sky-950/35', 'border' => 'border-blue-500 dark:border-sky-600', 'icon' => 'fa-info-circle', 'iconColor' => 'text-blue-600 dark:text-sky-400'],
                        };
                    @endphp
                    <div class="flex items-start space-x-4 p-4 {{ $alertStyles['bg'] }} border-l-4 {{ $alertStyles['border'] }} rounded-lg">
                        <i class="fas {{ $alertStyles['icon'] }} {{ $alertStyles['iconColor'] }} mt-1"></i>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 dark:text-slate-100">{{ $alerta['titulo'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-slate-300">{{ $alerta['descripcion'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-500 mt-1">{{ $alerta['fecha'] }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-slate-400 text-sm text-center py-4">No hay alertas recientes.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Gráfica de evolución IMC por paciente -->
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-4">
            <div>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-100">Evolución del IMC</h3>
                <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Elige un paciente para ver su progreso según las evaluaciones registradas, o el promedio mensual de todos.</p>
            </div>
            <div class="sm:min-w-[240px]">
                <label for="imc-paciente-select" class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Paciente</label>
                <select id="imc-paciente-select" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 text-sm px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="__promedio__">Promedio mensual (todos)</option>
                    @foreach($pacientesParaGrafica as $p)
                        <option value="{{ $p['id'] }}">{{ $p['nombre'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
               <p id="imc-chart-empty" class="hidden text-sm text-amber-700 dark:text-amber-300 mb-2"></p>
        <div id="imc-chart-summary" class="mb-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 p-4 text-sm text-slate-700 dark:text-slate-200"></div>
        <div class="relative w-full mx-auto" style="height: 320px;">
            <canvas id="imcChart"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var raw = {!! $imcChartDataJson !!};
    var ctx = document.getElementById('imcChart');
    if (!ctx || typeof Chart === 'undefined') return;

    var chartInstance = null;
    var emptyEl = document.getElementById('imc-chart-empty');
    var summaryEl = document.getElementById('imc-chart-summary');
    var currentPacienteKey = '__promedio__';

    function yBounds(values) {
        var nums = values.filter(function (v) { return v != null && !isNaN(v); });
        if (!nums.length) return { min: 12, max: 32 };
        var mn = Math.min.apply(null, nums);
        var mx = Math.max.apply(null, nums);
        if (mn === mx) return { min: Math.max(10, mn - 2), max: mx + 2 };
        return { min: Math.max(10, Math.floor(mn - 1)), max: Math.ceil(mx + 1) };
    }

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function gridColor() {
        return isDark() ? 'rgba(148, 163, 184, 0.2)' : 'rgba(0,0,0,0.06)';
    }

    function tickColor() {
        return isDark() ? '#94a3b8' : '#64748b';
    }

    function updateSummary(pacienteKey, serie) {
        if (!summaryEl) return;
        if (pacienteKey === '__promedio__') {
            summaryEl.innerHTML = '<p class="font-medium text-slate-800 dark:text-slate-100">Vista: promedio mensual de IMC (todos los pacientes con evaluaciones registradas).</p>' +
                '<p class="text-xs mt-2 text-slate-600 dark:text-slate-400">Selecciona un paciente para ver la evolución de su IMC y, en cada punto, el peso y la talla capturados en esa evaluación.</p>';
            return;
        }
        var d = (serie.data || []).filter(function (v) { return v != null && !isNaN(v); });
        if (!d.length) {
            summaryEl.innerHTML = '';
            return;
        }
        var first = d[0];
        var last = d[d.length - 1];
        var delta = d.length > 1 ? (last - first) : null;
        var deltaCls = delta == null ? '' : (delta > 0 ? 'text-amber-700 dark:text-amber-300' : (delta < 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400'));
        var deltaTxt = delta == null ? '' : ('<li class="' + deltaCls + '">Cambio desde la primera medición en gráfica: ' + (delta > 0 ? '+' : '') + delta.toFixed(2) + ' puntos de IMC</li>');
        summaryEl.innerHTML = '<p class="font-semibold text-slate-800 dark:text-slate-100">' + (serie.nombre || 'Paciente') + '</p>' +
            '<ul class="mt-2 space-y-1 text-slate-600 dark:text-slate-300 list-disc list-inside">' +
            '<li>Evaluaciones con peso y talla válidos: <strong>' + d.length + '</strong></li>' +
            '<li>IMC más reciente: <strong>' + last.toFixed(2) + '</strong> kg/m²</li>' +
            '<li>Primera medición en la serie: ' + first.toFixed(2) + ' kg/m²</li>' +
            deltaTxt + '</ul>';
    }

    function buildChart(pacienteKey) {
        currentPacienteKey = pacienteKey;
        var serie = raw[pacienteKey] || raw['__promedio__'];
        if (!serie) return;

        var labels = serie.labels || [];
        var data = serie.data || [];
        var label = pacienteKey === '__promedio__'
            ? 'IMC promedio (todos)'
            : ('IMC — ' + (serie.nombre || 'Paciente'));

        if (pacienteKey !== '__promedio__' && (!data.length)) {
            emptyEl.textContent = 'Este paciente aún no tiene evaluaciones con peso y talla válidos.';
            emptyEl.classList.remove('hidden');
        } else {
            emptyEl.classList.add('hidden');
        }

        updateSummary(pacienteKey, serie);

        var yb = yBounds(data);

        if (chartInstance) chartInstance.destroy();

        var tc = tickColor();
        var gc = gridColor();

        chartInstance = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.18)',
                    tension: 0.35,
                    spanGaps: true,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointHoverBorderWidth: 2,
                    pointHoverBackgroundColor: 'rgb(34, 197, 94)',
                    pointHoverBorderColor: isDark() ? '#f8fafc' : '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: { color: tc }
                    },
                    tooltip: {
                        backgroundColor: isDark() ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255,255,255,0.98)',
                        titleColor: isDark() ? '#f1f5f9' : '#0f172a',
                        bodyColor: isDark() ? '#e2e8f0' : '#334155',
                        borderColor: isDark() ? 'rgba(148, 163, 184, 0.35)' : 'rgba(0,0,0,0.08)',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function (context) {
                                var lines = [];
                                var y = context.parsed.y;
                                if (y != null && !isNaN(y)) lines.push('IMC: ' + Number(y).toFixed(2) + ' kg/m²');
                                var i = context.dataIndex;
                                var s = serie;
                                if (s.pesos && s.pesos[i] != null) lines.push('Peso: ' + s.pesos[i] + ' kg');
                                if (s.tallas_cm && s.tallas_cm[i] != null) lines.push('Talla: ' + s.tallas_cm[i] + ' cm');
                                return lines.length ? lines.join('\n') : '';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: tc, maxRotation: 45 },
                        grid: { color: gc }
                    },
                    y: {
                        min: yb.min,
                        max: yb.max,
                        title: {
                            display: true,
                            text: 'IMC (kg/m²)',
                            color: tc
                        },
                        ticks: { color: tc },
                        grid: { color: gc }
                    }
                }
            }
        });
    }

    document.getElementById('imc-paciente-select').addEventListener('change', function () {
        buildChart(this.value);
    });

    buildChart('__promedio__');

    new MutationObserver(function () {
        if (!chartInstance) return;
        var tc = tickColor();
        var gc = gridColor();
        chartInstance.options.scales.x.grid.color = gc;
        chartInstance.options.scales.y.grid.color = gc;
        chartInstance.options.scales.x.ticks.color = tc;
        chartInstance.options.scales.y.ticks.color = tc;
        if (chartInstance.options.scales.y.title) chartInstance.options.scales.y.title.color = tc;
        if (chartInstance.options.plugins.legend.labels) {
            chartInstance.options.plugins.legend.labels.color = tc;
        }
        if (chartInstance.options.plugins.tooltip) {
            chartInstance.options.plugins.tooltip.backgroundColor = isDark() ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255,255,255,0.98)';
            chartInstance.options.plugins.tooltip.titleColor = isDark() ? '#f1f5f9' : '#0f172a';
            chartInstance.options.plugins.tooltip.bodyColor = isDark() ? '#e2e8f0' : '#334155';
            chartInstance.options.plugins.tooltip.borderColor = isDark() ? 'rgba(148, 163, 184, 0.35)' : 'rgba(0,0,0,0.08)';
        }
        if (chartInstance.data.datasets[0]) {
            chartInstance.data.datasets[0].pointHoverBorderColor = isDark() ? '#f8fafc' : '#fff';
        }
        chartInstance.update();
        var serie = raw[currentPacienteKey] || raw['__promedio__'];
        if (serie) updateSummary(currentPacienteKey, serie);
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
})();
</script>
@endpush
