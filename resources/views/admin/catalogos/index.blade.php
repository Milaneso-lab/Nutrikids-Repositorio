@extends('layouts.app')

@section('title', 'Catálogos - Admin')

@section('page-title', 'Gestión de catálogos')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <x-stat-card label="Menús en catálogo" :value="$stats['menus']" icon="fa-utensils" color="emerald" />
    <x-stat-card label="Pacientes registrados" :value="$stats['pacientes']" icon="fa-child" color="sky" />
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="{{ route('admin.contenido.alimentos') }}" class="block p-6 bg-white dark:bg-slate-900 rounded-lg shadow-md border hover:border-emerald-500 transition">
        <i class="fas fa-apple-alt text-2xl text-emerald-600 mb-3"></i>
        <h3 class="font-semibold">Alimentos</h3>
        <p class="text-sm text-slate-500 mt-1">Catálogo de alimentos y referencias nutricionales.</p>
    </a>
    <a href="{{ route('admin.contenido.recetas') }}" class="block p-6 bg-white dark:bg-slate-900 rounded-lg shadow-md border hover:border-emerald-500 transition">
        <i class="fas fa-book-open text-2xl text-sky-600 mb-3"></i>
        <h3 class="font-semibold">Recetas</h3>
        <p class="text-sm text-slate-500 mt-1">Recetas saludables para familias.</p>
    </a>
    <a href="{{ route('admin.contenido.menus') }}" class="block p-6 bg-white dark:bg-slate-900 rounded-lg shadow-md border hover:border-emerald-500 transition">
        <i class="fas fa-clipboard-list text-2xl text-amber-600 mb-3"></i>
        <h3 class="font-semibold">Menús globales</h3>
        <p class="text-sm text-slate-500 mt-1">Plantillas y menús del sistema ({{ $stats['menus'] }} registros).</p>
    </a>
</div>
@endsection
