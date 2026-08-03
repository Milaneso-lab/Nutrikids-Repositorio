@extends('layouts.app')

@section('title', 'Reportes - Nutriólogo')

@section('page-title', 'Reportes')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 mb-6 border border-slate-200/80 dark:border-slate-800">
        <h3 class="text-lg font-semibold mb-2">Resumen visual de reportes</h3>
        <p class="text-sm text-slate-500 mb-4">Reportes generados por mes (últimos 6 meses).</p>
        <div style="height:200px"><canvas id="reportesChart"></canvas></div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-1">
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
                <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100">Generar nuevo reporte</h3>
                    <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Crea un reporte de progreso con la información actual de las evaluaciones del paciente.</p>
                </div>
                <form action="{{ route('nutriologo.reportes.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf

                    <div>
                        <label for="paciente_id" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Paciente</label>
                        <select name="paciente_id" id="paciente_id" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500" required>
                            <option value="">Selecciona un paciente</option>
                            @foreach($pacientes as $paciente)
                                <option value="{{ $paciente->id }}" @selected(old('paciente_id') == $paciente->id)>
                                    {{ trim($paciente->nombre . ' ' . $paciente->apellidos) }} ({{ $paciente->evaluaciones_count }} evaluaciones)
                                </option>
                            @endforeach
                        </select>
                        @if($pacientes->isEmpty())
                            <p class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                                No hay pacientes disponibles con evaluaciones registradas. Primero debes registrar pacientes y al menos una evaluación por paciente.
                            </p>
                        @endif
                        @error('paciente_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="titulo" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Título del reporte</label>
                        <input
                            type="text"
                            name="titulo"
                            id="titulo"
                            value="{{ old('titulo') }}"
                            placeholder="Ej. Reporte mensual de progreso"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 placeholder:text-gray-500 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-green-500"
                        >
                        @error('titulo')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 dark:bg-emerald-600 text-white rounded-lg hover:bg-green-700 dark:hover:bg-emerald-500 transition disabled:opacity-50 disabled:cursor-not-allowed" @disabled($pacientes->isEmpty())>
                        <i class="fas fa-plus mr-2"></i>Generar reporte
                    </button>
                </form>
            </div>
        </div>

        <div class="xl:col-span-2">
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
                <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100">Reportes disponibles</h3>
                    <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Consulta los reportes ya generados por paciente.</p>
                </div>
                <div class="p-6">
                    @if($reportes->isEmpty())
                        <div class="border border-dashed border-gray-300 dark:border-slate-600 rounded-lg p-8 text-center text-gray-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-800/40">
                            Todavía no hay reportes generados. Usa el formulario de la izquierda para crear el primero.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($reportes as $reporte)
                                <div class="border border-gray-200 dark:border-slate-700 rounded-lg p-6 hover:border-green-500 dark:hover:border-emerald-500 hover:shadow-md transition bg-white dark:bg-slate-800/40">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-green-100 dark:bg-emerald-900/50 rounded-full flex items-center justify-center shrink-0">
                                            <i class="fas fa-file-alt text-green-600 dark:text-emerald-300 text-xl"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="font-semibold text-gray-800 dark:text-slate-100 truncate">{{ $reporte->titulo ?: 'Reporte de progreso' }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-slate-400 truncate">{{ trim(($reporte->paciente->nombre ?? '') . ' ' . ($reporte->paciente->apellidos ?? '')) ?: 'Paciente sin nombre' }}</p>
                                            <p class="text-xs text-gray-500 dark:text-slate-500">Generado {{ optional($reporte->created_at)->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between gap-3">
                                        <a href="{{ route('nutriologo.reportes.show', $reporte) }}" class="inline-flex items-center text-green-600 dark:text-emerald-400 hover:text-green-700 dark:hover:text-emerald-300 text-sm font-medium">
                                            Ver reporte <i class="fas fa-arrow-right ml-1"></i>
                                        </a>
                                        <a href="{{ route('nutriologo.reportes.pdf', $reporte) }}" class="inline-flex items-center text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm font-medium">
                                            <i class="fas fa-file-pdf mr-1"></i>PDF
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function(){
    var raw = {!! $statsJson ?? '{"labels":[],"totales":[]}' !!};
    var el = document.getElementById('reportesChart');
    if (!el || typeof Chart === 'undefined') return;
    new Chart(el.getContext('2d'), {
        type: 'bar',
        data: { labels: raw.labels, datasets: [{ label: 'Reportes', data: raw.totales, backgroundColor: 'rgba(16,185,129,0.7)' }] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
})();
</script>
@endpush
