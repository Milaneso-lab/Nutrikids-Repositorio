@extends('layouts.app')

@section('title', 'Gestión de Alimentos - Administrador')

@section('page-title', 'Gestión de Alimentos')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <!-- Sub-navegación -->
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md mb-6 border border-slate-200/80 dark:border-slate-800">
        <div class="border-b border-gray-200 dark:border-slate-700">
            <nav class="flex -mb-px">
                <a href="{{ route('admin.contenido.alimentos') }}" class="tab-button active px-6 py-4 text-sm font-medium text-green-600 dark:text-emerald-400 border-b-2 border-green-600 dark:border-emerald-500 bg-slate-50/50 dark:bg-slate-800/40">
                    <i class="fas fa-apple-alt mr-2"></i>Alimentos
                </a>
                <a href="{{ route('admin.contenido.recetas') }}" class="tab-button px-6 py-4 text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition">
                    <i class="fas fa-utensils mr-2"></i>Recetas
                </a>
                <a href="{{ route('admin.contenido.menus') }}" class="tab-button px-6 py-4 text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition">
                    <i class="fas fa-calendar-alt mr-2"></i>Menús
                </a>
            </nav>
        </div>
    </div>

    <!-- Lista de alimentos -->
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100">Base de Datos de Alimentos</h3>
                <button type="button" class="px-4 py-2 bg-green-600 dark:bg-emerald-600 text-white rounded-lg hover:bg-green-700 dark:hover:bg-emerald-500">
                    <i class="fas fa-plus mr-2"></i>Agregar Alimento
                </button>
            </div>
            <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Gestiona la información nutricional de los alimentos disponibles.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                <thead class="bg-slate-100 dark:bg-slate-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Alimento</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Calorías/100g</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Proteínas</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-800">
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 dark:bg-emerald-900/50 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-apple-alt text-green-600 dark:text-emerald-300"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-slate-100">Manzana</div>
                                    <div class="text-sm text-gray-500 dark:text-slate-400">Fruta fresca</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-emerald-950/60 text-green-900 dark:text-emerald-100 ring-1 ring-black/5 dark:ring-white/10">
                                Frutas
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-200">52 kcal</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-200">0.2g</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mr-3">Editar</button>
                            <button type="button" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">Eliminar</button>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-sky-900/50 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-drumstick-bite text-blue-600 dark:text-sky-300"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-slate-100">Pollo</div>
                                    <div class="text-sm text-gray-500 dark:text-slate-400">Carne blanca</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-sky-950/60 text-blue-900 dark:text-sky-100 ring-1 ring-black/5 dark:ring-white/10">
                                Proteínas
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-200">165 kcal</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-200">31g</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mr-3">Editar</button>
                            <button type="button" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">Eliminar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection