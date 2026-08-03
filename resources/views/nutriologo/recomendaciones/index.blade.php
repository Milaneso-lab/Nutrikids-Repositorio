@extends('layouts.app')

@section('title', 'Recomendaciones - Nutriólogo')

@section('page-title', 'Recomendaciones clínicas')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
<p class="text-sm text-slate-600 dark:text-slate-400 mb-6">Las recomendaciones registradas aquí se sincronizan con el portal del padre vía evaluaciones clínicas.</p>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-white dark:bg-slate-900 rounded-lg shadow-md border p-6">
        <h3 class="font-semibold mb-4">Nueva recomendación</h3>
        <form action="{{ route('nutriologo.recomendaciones.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Paciente</label>
                <select name="paciente_id" class="w-full mt-1 px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600" required>
                    <option value="">Seleccionar…</option>
                    @foreach($pacientes as $p)
                        <option value="{{ $p->id }}" @selected(old('paciente_id', request('paciente_id')) == $p->id)>{{ trim($p->nombre.' '.$p->apellidos) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Recomendación</label>
                <textarea name="recomendaciones" rows="5" required minlength="10" class="w-full mt-1 px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600" placeholder="Indicaciones para la familia…">{{ old('recomendaciones') }}</textarea>
            </div>
            <button type="submit" class="w-full py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Registrar</button>
        </form>
    </div>
    <div class="lg:col-span-2">
        <x-list-toolbar :action="route('nutriologo.recomendaciones.index')">
            <div>
                <label class="text-xs font-medium text-slate-500">Buscar</label>
                <input type="search" name="q" value="{{ request('q') }}" class="w-full px-3 py-2 border rounded-lg dark:bg-slate-800 text-sm">
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
        <div class="space-y-3">
            @forelse($recomendaciones as $rec)
                <div class="bg-white dark:bg-slate-900 rounded-lg border p-4">
                    <div class="flex justify-between gap-2">
                        <p class="font-semibold text-sm">{{ trim(($rec->paciente->nombre ?? '').' '.($rec->paciente->apellidos ?? '')) }}</p>
                        <span class="text-xs text-slate-500">{{ optional($rec->created_at)->format('d/m/Y H:i') }}</span>
                    </div>
                    <p class="text-sm mt-2 text-slate-700 dark:text-slate-300">{{ $rec->recomendaciones }}</p>
                    <a href="{{ route('nutriologo.pacientes.show', $rec->paciente_id) }}?tab=recomendaciones" class="text-xs text-emerald-600 hover:underline mt-2 inline-block">Ver expediente</a>
                </div>
            @empty
                <x-empty-state icon="fa-comment-medical" title="Sin recomendaciones" message="Registra la primera recomendación con el formulario." />
            @endforelse
        </div>
        @if($recomendaciones->hasPages())
            <div class="mt-4">{{ $recomendaciones->links() }}</div>
        @endif
    </div>
</div>
@endsection
