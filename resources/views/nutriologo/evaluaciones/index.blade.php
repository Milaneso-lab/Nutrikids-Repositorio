@extends('layouts.app')

@section('title', 'Evaluaciones - Nutriólogo')

@section('page-title', 'Evaluaciones Nutricionales')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('nutriologo.evaluaciones.create') }}" class="px-6 py-2 bg-green-600 dark:bg-emerald-600 text-white rounded-lg hover:bg-green-700 dark:hover:bg-emerald-500 flex items-center space-x-2">
            <i class="fas fa-plus"></i>
            <span>Nueva Evaluación</span>
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100">Listado de evaluaciones</h3>
            <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Historial de evaluaciones nutricionales por paciente.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                <thead class="bg-slate-100 dark:bg-slate-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Paciente</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Peso (kg)</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Talla (cm)</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">IMC</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-800">
                    @forelse($evaluaciones as $evaluacion)
                        @php
                            $peso = is_numeric($evaluacion->peso) ? (float) $evaluacion->peso : null;
                            $talla = is_numeric($evaluacion->talla) ? (float) $evaluacion->talla : null;
                            $tallaMetros = $talla ? ($talla > 3 ? $talla / 100 : $talla) : null;
                            $imc = ($peso && $tallaMetros && $tallaMetros > 0) ? round($peso / ($tallaMetros * $tallaMetros), 2) : null;
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-slate-100">{{ trim(($evaluacion->paciente->nombre ?? '') . ' ' . ($evaluacion->paciente->apellidos ?? '')) ?: 'Paciente sin nombre' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-slate-400">{{ optional($evaluacion->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-200">{{ $evaluacion->peso }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-200">{{ $evaluacion->talla }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-200">{{ $imc !== null ? number_format($imc, 2) : 'Sin dato' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('nutriologo.evaluaciones.edit', $evaluacion) }}" class="text-green-600 dark:text-emerald-400 hover:text-green-800 dark:hover:text-emerald-300"><i class="fas fa-edit mr-1"></i>Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-6 text-sm text-center text-gray-500 dark:text-slate-400">No hay evaluaciones registradas todavía.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-gray-50 dark:bg-slate-800/90 px-4 py-3 text-sm text-gray-600 dark:text-slate-300 border-t border-gray-200 dark:border-slate-700">
            Si no hay evaluaciones, usa el botón <strong class="text-gray-900 dark:text-slate-100">Nueva Evaluación</strong> para registrar la primera.
        </div>
    </div>
@endsection
