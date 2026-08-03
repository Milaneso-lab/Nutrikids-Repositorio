@extends('layouts.app')

@section('title', 'Auditoría - Administrador')

@section('page-title', 'Bitácora de actividad')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <x-page-header title="Auditoría del sistema" subtitle="Registro agregado de eventos recientes (usuarios, citas, clínica y comunidad)." />

    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-slate-200/80 dark:border-slate-800 overflow-hidden">
        @forelse($entradas as $entrada)
            <div class="flex items-start gap-4 px-6 py-4 border-b border-slate-100 dark:border-slate-800 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0">
                    <i class="fas {{ $entrada['icon'] }} text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold text-gray-800 dark:text-slate-100">{{ $entrada['accion'] }}</p>
                        <time class="text-xs text-gray-500 dark:text-slate-500">{{ $entrada['fecha']->diffForHumans() }}</time>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">{{ $entrada['detalle'] }}</p>
                    <span class="inline-block mt-2 text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded">{{ $entrada['tipo'] }}</span>
                </div>
            </div>
        @empty
            <x-empty-state icon="fa-clipboard-list" title="Sin eventos recientes" message="La actividad del sistema aparecerá aquí conforme se registren usuarios, citas y evaluaciones." />
        @endforelse
    </div>
@endsection
