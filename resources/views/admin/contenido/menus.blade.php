@extends('layouts.app')

@section('title', 'Gestión de Menús - Administrador')

@section('page-title', 'Gestión de Menús')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <!-- Sub-navegación -->
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md mb-6 border border-slate-200/80 dark:border-slate-800">
        <div class="border-b border-gray-200 dark:border-slate-700">
            <nav class="flex -mb-px">
                <a href="{{ route('admin.contenido.alimentos') }}" class="tab-button px-6 py-4 text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition">
                    <i class="fas fa-apple-alt mr-2"></i>Alimentos
                </a>
                <a href="{{ route('admin.contenido.recetas') }}" class="tab-button px-6 py-4 text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition">
                    <i class="fas fa-utensils mr-2"></i>Recetas
                </a>
                <a href="{{ route('admin.contenido.menus') }}" class="tab-button active px-6 py-4 text-sm font-medium text-green-600 dark:text-emerald-400 border-b-2 border-green-600 dark:border-emerald-500 bg-slate-50/50 dark:bg-slate-800/40">
                    <i class="fas fa-calendar-alt mr-2"></i>Menús
                </a>
            </nav>
        </div>
    </div>

    <!-- Lista de menús -->
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100">Menús clínicos registrados</h3>
                <span class="text-sm text-gray-500 dark:text-slate-400">Los menús se crean desde el panel clínico del nutriólogo.</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Vista administrativa de los menús creados desde el módulo clínico del nutriólogo.</p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @forelse($menus as $menu)
                    <div class="border border-gray-200 dark:border-slate-700 rounded-lg p-6 bg-gray-50/50 dark:bg-slate-800/40">
                        <div class="flex items-center justify-between mb-4 gap-2">
                            <div class="min-w-0">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-slate-100">{{ $menu->nombre }}</h4>
                                <p class="text-sm text-gray-600 dark:text-slate-400">
                                    Paciente: {{ trim(($menu->paciente->nombre ?? '') . ' ' . ($menu->paciente->apellidos ?? '')) ?: 'Sin paciente asignado' }}
                                </p>
                            </div>
                            <span class="px-3 py-1 bg-green-100 dark:bg-emerald-950/55 text-green-900 dark:text-emerald-100 text-sm rounded-full shrink-0 ring-1 ring-black/5 dark:ring-white/10">
                                Registrado
                            </span>
                        </div>

                        <div class="bg-white dark:bg-slate-900/80 rounded-lg p-4 text-sm text-gray-700 dark:text-slate-300 whitespace-pre-line min-h-[120px] border border-gray-100 dark:border-slate-700">{{ $menu->descripcion }}</div>

                        <div class="mt-6 flex space-x-3">
                            <button type="button" onclick="eliminarMenu({{ $menu->id }})" class="px-4 py-2 bg-red-600 dark:bg-red-700 text-white text-sm rounded hover:bg-red-700 dark:hover:bg-red-600">
                                Eliminar
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full border border-dashed border-gray-300 dark:border-slate-600 rounded-lg p-8 text-center text-gray-500 dark:text-slate-400 bg-slate-50/30 dark:bg-slate-800/30">
                        No hay menús registrados todavía.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function eliminarMenu(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este menú?')) {
            fetch(`/admin/contenido/menus/${id}/eliminar`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al eliminar el menú');
                }
            });
        }
    }
</script>
@endpush