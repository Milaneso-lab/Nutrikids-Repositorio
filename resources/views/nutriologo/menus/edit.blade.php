@extends('layouts.app')

@section('title', 'Editar Menú - Nutriólogo')

@section('page-title', 'Editar plan alimenticio')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2">
        <form action="{{ route('nutriologo.menus.update', $menu) }}" method="POST" class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Nombre del plan</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $menu->nombre) }}" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Paciente asignado</label>
                    <select name="paciente_id" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600" required>
                        @foreach($pacientes as $paciente)
                            <option value="{{ $paciente->id }}" @selected(old('paciente_id', $menu->paciente_id) == $paciente->id)>{{ trim($paciente->nombre . ' ' . $paciente->apellidos) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Estado</label>
                    <select name="estado" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600">
                        @foreach(['activo','borrador','archivado'] as $st)
                            <option value="{{ $st }}" @selected(old('estado', $menu->estado ?? 'activo') === $st)>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Descripción del plan</label>
                <textarea name="descripcion" rows="12" class="w-full px-4 py-3 border rounded-lg dark:bg-slate-800 dark:border-slate-600">{{ old('descripcion', $menu->descripcion) }}</textarea>
            </div>
            <div class="flex flex-wrap gap-3 justify-end border-t pt-6">
                <a href="{{ route('nutriologo.menus.index') }}" class="px-6 py-2 border rounded-lg">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"><i class="fas fa-save mr-2"></i>Guardar</button>
            </div>
        </form>
        <form action="{{ route('nutriologo.menus.duplicate', $menu) }}" method="POST" class="mt-3 text-right">@csrf<button type="submit" class="text-sm text-sky-600 hover:underline"><i class="fas fa-copy mr-1"></i>Duplicar plan</button></form>
    </div>
    <div>
        @if($menu->original)
            <div class="bg-sky-50 dark:bg-sky-950/30 border border-sky-200 dark:border-sky-800 rounded-lg p-4 mb-4 text-sm">
                Duplicado de: <a href="{{ route('nutriologo.menus.edit', $menu->original) }}" class="text-sky-700 dark:text-sky-300 hover:underline">{{ $menu->original->nombre }}</a>
            </div>
        @endif
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border p-4">
            <h4 class="font-semibold mb-3 text-sm">Historial del paciente</h4>
            <div class="space-y-2">
                @forelse($historial as $h)
                    <a href="{{ route('nutriologo.menus.edit', $h) }}" class="block p-2 rounded hover:bg-slate-50 dark:hover:bg-slate-800 text-sm">
                        {{ $h->nombre }} · <x-menu-estado-badge :estado="$h->estado ?? 'activo'" />
                    </a>
                @empty
                    <p class="text-xs text-slate-500">Sin otros planes para este paciente.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
