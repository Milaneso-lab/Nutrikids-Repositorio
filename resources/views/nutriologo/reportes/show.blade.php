@extends('layouts.app')

@section('title', 'Ver Reporte - Nutriólogo')

@section('page-title', 'Detalle del Reporte')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
    <div class="mb-4">
        <a href="{{ route('nutriologo.reportes.index') }}" class="text-green-600 dark:text-emerald-400 hover:text-green-700 dark:hover:text-emerald-300 text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Volver a reportes
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/80">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-100">{{ $reporte->titulo ?: 'Reporte de progreso nutricional' }}</h3>
            <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Resumen actualizado de evaluaciones y evolución del paciente.</p>
        </div>
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-green-50 dark:bg-emerald-950/35 rounded-lg p-4 border border-green-100/80 dark:border-emerald-800/50">
                    <p class="text-sm text-gray-600 dark:text-slate-400">Paciente</p>
                    <p class="font-semibold text-gray-800 dark:text-slate-100">{{ $reportData['paciente_nombre'] }}</p>
                </div>
                <div class="bg-blue-50 dark:bg-sky-950/35 rounded-lg p-4 border border-blue-100/80 dark:border-sky-800/50">
                    <p class="text-sm text-gray-600 dark:text-slate-400">Período</p>
                    <p class="font-semibold text-gray-800 dark:text-slate-100">{{ $reportData['periodo'] }}</p>
                </div>
                <div class="bg-yellow-50 dark:bg-amber-950/30 rounded-lg p-4 border border-yellow-100/80 dark:border-amber-800/50">
                    <p class="text-sm text-gray-600 dark:text-slate-400">IMC promedio</p>
                    <p class="font-semibold text-gray-800 dark:text-slate-100">{{ $reportData['imc_promedio'] !== null ? number_format($reportData['imc_promedio'], 2) : 'Sin datos' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-slate-400">Reporte generado</p>
                    <p class="font-semibold text-gray-800 dark:text-slate-100">{{ optional($reporte->created_at)->format('d/m/Y H:i') }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-slate-400">Total de evaluaciones analizadas</p>
                    <p class="font-semibold text-gray-800 dark:text-slate-100">{{ $reportData['total_evaluaciones'] }}</p>
                </div>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800 dark:text-slate-100 mb-3">Evolución de peso y talla</h4>
                <div class="border border-gray-200 dark:border-slate-700 rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-slate-100 dark:bg-slate-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Fecha</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Peso (kg)</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Talla (cm)</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">IMC</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Recomendaciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-800">
                            @forelse($reportData['evaluaciones'] as $evaluacion)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-slate-200">{{ $evaluacion['fecha'] ?? 'Sin fecha' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-slate-200">{{ $evaluacion['peso'] ?? 'Sin dato' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-slate-200">{{ $evaluacion['talla'] ?? 'Sin dato' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-slate-200">{{ $evaluacion['imc'] !== null ? number_format($evaluacion['imc'], 2) : 'Sin dato' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-slate-300">{{ $evaluacion['recomendaciones'] ?: 'Sin recomendaciones' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-sm text-center text-gray-500 dark:text-slate-400">Este paciente no tiene evaluaciones registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex justify-end">
                <a href="{{ route('nutriologo.reportes.pdf', $reporte) }}" class="px-4 py-2 bg-red-600 dark:bg-red-700 text-white rounded-lg hover:bg-red-700 dark:hover:bg-red-600 text-sm inline-flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                </a>
            </div>
        </div>
    </div>
@endsection
