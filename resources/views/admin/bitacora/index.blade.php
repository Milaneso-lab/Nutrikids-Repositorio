@extends('layouts.app')

@section('title', 'Bitácora - Admin')

@section('page-title', 'Bitácora del proyecto')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
<div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border p-6">
    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">Registro de hitos del proyecto ({{ $path }}).</p>
    <div class="space-y-4">
        @forelse($entries as $entry)
            <article class="border-l-4 border-emerald-500 pl-4 py-2">
                <header class="flex flex-wrap items-baseline gap-2 mb-2">
                    <time class="text-xs font-mono text-slate-500">{{ $entry['fecha'] }}</time>
                    <h3 class="font-semibold text-slate-800 dark:text-slate-100">{{ $entry['titulo'] }}</h3>
                </header>
                <div class="prose prose-sm dark:prose-invert max-w-none whitespace-pre-line text-slate-700 dark:text-slate-300">{{ \Illuminate\Support\Str::limit($entry['contenido'], 1200) }}</div>
            </article>
        @empty
            <x-empty-state icon="fa-book" title="Sin entradas" message="No se encontraron entradas en la bitácora." />
        @endforelse
    </div>
</div>
@endsection
