@extends('layouts.app')

@section('title', 'Gestión de Nutriólogos - Administrador')

@section('page-title', 'Gestión de Nutriólogos')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <x-page-header title="Profesionales de nutrición" subtitle="Alta, edición y seguimiento de cuentas con rol nutriólogo.">
        <a href="{{ route('admin.nutriologos.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium">
            <i class="fas fa-plus mr-2"></i>Agregar nutriólogo
        </a>
    </x-page-header>

    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                <thead class="bg-slate-100 dark:bg-slate-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Nutriólogo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Citas activas</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-800">
                    @forelse($nutriologos as $item)
                        @php $n = $item['usuario']; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/80">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/50 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user-md text-emerald-600 dark:text-emerald-300"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-slate-100">
                                            {{ $n->nombre }} {{ $n->apellido_paterno }} {{ $n->apellido_materno }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-slate-400">{{ $n->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-slate-200">{{ $item['citas_activas'] }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-emerald-950/55 text-green-900 dark:text-emerald-100">Activo</span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('admin.nutriologos.edit', $n->id_usuario) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state icon="fa-user-md" title="No hay nutriólogos registrados" message="Crea la primera cuenta profesional para asignar citas.">
                                    <a href="{{ route('admin.nutriologos.create') }}" class="inline-block px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm">Registrar nutriólogo</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
