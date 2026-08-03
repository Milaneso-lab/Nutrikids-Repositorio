@extends('layouts.app')

@section('title', 'Gestión de Recetas - Administrador')

@section('page-title', 'Gestión de Recetas')

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
                <a href="{{ route('admin.contenido.recetas') }}" class="tab-button active px-6 py-4 text-sm font-medium text-green-600 dark:text-emerald-400 border-b-2 border-green-600 dark:border-emerald-500 bg-slate-50/50 dark:bg-slate-800/40">
                    <i class="fas fa-utensils mr-2"></i>Recetas
                </a>
                <a href="{{ route('admin.contenido.menus') }}" class="tab-button px-6 py-4 text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition">
                    <i class="fas fa-calendar-alt mr-2"></i>Menús
                </a>
            </nav>
        </div>
    </div>

    <!-- Lista de recetas -->
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100">Recetas Disponibles</h3>
                <button type="button" class="px-4 py-2 bg-green-600 dark:bg-emerald-600 text-white rounded-lg hover:bg-green-700 dark:hover:bg-emerald-500">
                    <i class="fas fa-plus mr-2"></i>Crear Receta
                </button>
            </div>
            <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Gestiona las recetas disponibles para los menús nutricionales.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
            <!-- Receta 1 -->
            <div class="bg-gray-50 dark:bg-slate-800/70 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
                <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-green-100 dark:bg-emerald-900/50 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-utensils text-green-600 dark:text-emerald-300"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-slate-100">Ensalada de Pollo</h4>
                        <span class="text-sm text-gray-500 dark:text-slate-400">Plato principal</span>
                    </div>
                </div>
                <div class="space-y-2 text-sm text-gray-600 dark:text-slate-300">
                    <div class="flex justify-between">
                        <span>Calorías:</span>
                        <span>320 kcal</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Proteínas:</span>
                        <span>28g</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tiempo:</span>
                        <span>25 min</span>
                    </div>
                </div>
                <div class="mt-4 flex space-x-2">
                    <button class="flex-1 px-3 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                        Editar
                    </button>
                    <button class="flex-1 px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                        Eliminar
                    </button>
                </div>
            </div>

            <!-- Receta 2 -->
            <div class="bg-gray-50 dark:bg-slate-800/70 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
                <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-sky-900/50 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-carrot text-blue-600 dark:text-sky-300"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-slate-100">Sopa de Verduras</h4>
                        <span class="text-sm text-gray-500 dark:text-slate-400">Entrada</span>
                    </div>
                </div>
                <div class="space-y-2 text-sm text-gray-600 dark:text-slate-300">
                    <div class="flex justify-between">
                        <span>Calorías:</span>
                        <span>120 kcal</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Proteínas:</span>
                        <span>4g</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tiempo:</span>
                        <span>15 min</span>
                    </div>
                </div>
                <div class="mt-4 flex space-x-2">
                    <button class="flex-1 px-3 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                        Editar
                    </button>
                    <button class="flex-1 px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                        Eliminar
                    </button>
                </div>
            </div>

            <!-- Receta 3 -->
            <div class="bg-gray-50 dark:bg-slate-800/70 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
                <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-amber-900/50 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-lemon text-yellow-600 dark:text-amber-300"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-slate-100">Yogurt con Frutas</h4>
                        <span class="text-sm text-gray-500 dark:text-slate-400">Postre</span>
                    </div>
                </div>
                <div class="space-y-2 text-sm text-gray-600 dark:text-slate-300">
                    <div class="flex justify-between">
                        <span>Calorías:</span>
                        <span>180 kcal</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Proteínas:</span>
                        <span>8g</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tiempo:</span>
                        <span>5 min</span>
                    </div>
                </div>
                <div class="mt-4 flex space-x-2">
                    <button class="flex-1 px-3 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                        Editar
                    </button>
                    <button class="flex-1 px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection