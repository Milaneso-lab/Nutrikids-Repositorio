@extends('layouts.app')

@section('title', 'Pacientes - Nutriólogo')

@section('page-title', 'Gestión de Pacientes')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <p class="text-sm text-gray-600 dark:text-slate-400">Busca, filtra y accede al expediente clínico de cada paciente.</p>
        <a href="{{ route('nutriologo.pacientes.create') }}" class="px-6 py-2 bg-green-600 dark:bg-emerald-600 text-white rounded-lg hover:bg-green-700 dark:hover:bg-emerald-500 transition inline-flex items-center justify-center space-x-2 shrink-0">
            <i class="fas fa-plus"></i>
            <span>Nuevo Paciente</span>
        </a>
    </div>

    <x-list-toolbar :action="route('nutriologo.pacientes.index')">
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Buscar</label>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Nombre o apellidos…"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Estado</label>
            <select name="estado" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                <option value="">Todos</option>
                @foreach(['activo' => 'Activo', 'seguimiento' => 'En seguimiento', 'inactivo' => 'Inactivo', 'alta' => 'Alta clínica'] as $val => $lbl)
                    <option value="{{ $val }}" @selected(request('estado') === $val)>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Ordenar</label>
            <select name="sort" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                <option value="nombre" @selected(request('sort', 'nombre') === 'nombre')>Nombre A-Z</option>
                <option value="reciente" @selected(request('sort') === 'reciente')>Más recientes</option>
                <option value="edad" @selected(request('sort') === 'edad')>Edad (menor primero)</option>
            </select>
        </div>
    </x-list-toolbar>

    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                <thead class="bg-slate-100 dark:bg-slate-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Paciente</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Edad</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">IMC</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Nutricional</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Última eval.</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-800">
                    @forelse($pacientes as $paciente)
                        @php $r = $resumenes[$paciente->id] ?? []; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-green-100 dark:bg-emerald-900/50 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-child text-green-600 dark:text-emerald-300"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-slate-100">{{ trim($paciente->nombre . ' ' . $paciente->apellidos) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-200">
                                {{ $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->age . ' años' : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-200">
                                {{ isset($r['imc']) ? number_format($r['imc'], 2) : 'Sin dato' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="text-gray-700 dark:text-slate-300">{{ $r['clasificacion_label'] ?? 'Sin evaluación' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-patient-status-badge :estado="$paciente->estado_paciente ?? 'activo'" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-slate-400">{{ $r['ultima_fecha'] ?? 'Sin evaluación' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="{{ route('nutriologo.pacientes.show', $paciente) }}" class="text-green-600 dark:text-emerald-400 hover:underline" title="Expediente"><i class="fas fa-folder-open"></i></a>
                                <a href="{{ route('nutriologo.evaluaciones.create', ['paciente_id' => $paciente->id]) }}" class="text-sky-600 dark:text-sky-400 hover:underline" title="Nueva evaluación"><i class="fas fa-plus-circle"></i></a>
                                <a href="{{ route('nutriologo.menus.create', ['paciente_id' => $paciente->id]) }}" class="text-amber-600 dark:text-amber-400 hover:underline" title="Nuevo plan"><i class="fas fa-utensils"></i></a>
                                <a href="{{ route('nutriologo.pacientes.edit', $paciente) }}" class="text-blue-600 dark:text-sky-400 hover:underline" title="Editar"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8">
                                <x-empty-state icon="fa-child" title="Sin pacientes" message="Registra el primer paciente para comenzar el seguimiento clínico." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pacientes->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700">{{ $pacientes->links() }}</div>
        @else
            <div class="bg-gray-50 dark:bg-slate-800/90 px-4 py-3 text-sm text-gray-600 dark:text-slate-300 border-t border-gray-200 dark:border-slate-700">
                Total: <strong class="text-gray-900 dark:text-slate-100">{{ $pacientes->total() }}</strong> pacientes
            </div>
        @endif
    </div>
@endsection
