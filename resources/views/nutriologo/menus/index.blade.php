@extends('layouts.app')

@section('title', 'Menús - Nutriólogo')

@section('page-title', 'Planes alimenticios')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between gap-4">
        <p class="text-sm text-slate-600 dark:text-slate-400">Gestiona planes con estado, historial y duplicación.</p>
        <a href="{{ route('nutriologo.menus.create') }}" class="px-6 py-2 bg-green-600 dark:bg-emerald-600 text-white rounded-lg hover:bg-green-700 inline-flex items-center justify-center space-x-2 shrink-0">
            <i class="fas fa-plus"></i><span>Nuevo plan</span>
        </a>
    </div>

    <x-list-toolbar :action="route('nutriologo.menus.index')">
        <div>
            <label class="text-xs font-medium text-slate-500">Estado</label>
            <select name="estado" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-800 text-sm">
                <option value="">Todos</option>
                @foreach(['activo','borrador','archivado'] as $st)
                    <option value="{{ $st }}" @selected(request('estado') === $st)>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-slate-500">Paciente</label>
            <select name="paciente_id" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-800 text-sm">
                <option value="">Todos</option>
                @foreach($pacientes as $p)
                    <option value="{{ $p->id }}" @selected(request('paciente_id') == $p->id)>{{ trim($p->nombre.' '.$p->apellidos) }}</option>
                @endforeach
            </select>
        </div>
        <div></div>
    </x-list-toolbar>

    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                <thead class="bg-slate-100 dark:bg-slate-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Paciente</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Registro</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-800">
                    @forelse($menus as $menu)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/80">
                            <td class="px-6 py-4 text-sm font-medium">{{ $menu->nombre }}</td>
                            <td class="px-6 py-4 text-sm">{{ trim(($menu->paciente->nombre ?? '').' '.($menu->paciente->apellidos ?? '')) }}</td>
                            <td class="px-6 py-4"><x-menu-estado-badge :estado="$menu->estado ?? 'activo'" /></td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ optional($menu->created_at)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm space-x-3">
                                <a href="{{ route('nutriologo.menus.edit', $menu) }}" class="text-emerald-600 hover:underline">Editar</a>
                                <form action="{{ route('nutriologo.menus.duplicate', $menu) }}" method="POST" class="inline">@csrf<button type="submit" class="text-sky-600 hover:underline">Duplicar</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No hay planes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
