@extends('layouts.app')

@section('title', 'Menús - Nutriólogo')

@section('page-title', 'Menús y Planes Alimenticios')

@section('navigation')
    <a href="{{ route('nutriologo.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('nutriologo.pacientes.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-child"></i>
        <span>Pacientes</span>
    </a>
    <a href="{{ route('nutriologo.evaluaciones.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-clipboard-check"></i>
        <span>Evaluaciones</span>
    </a>
    <a href="{{ route('nutriologo.menus.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-green-500 rounded-lg text-white">
        <i class="fas fa-utensils"></i>
        <span>Menús</span>
    </a>
    <a href="{{ route('nutriologo.reportes.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-chart-bar"></i>
        <span>Reportes</span>
    </a>
@endsection

@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('nutriologo.menus.create') }}" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center space-x-2">
            <i class="fas fa-plus"></i>
            <span>Nuevo Menú</span>
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Menús asignados</h3>
            <p class="text-sm text-gray-600 mt-1">Planes alimenticios semanales por paciente.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre del menú</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vigencia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Menú Semanal Balanceado</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">María González</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">18 Nov - 24 Nov 2024</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('nutriologo.menus.edit', 1) }}" class="text-green-600 hover:text-green-900"><i class="fas fa-edit mr-1"></i>Editar</a>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Plan bajo en azúcares</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">Ana Martínez</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">11 Nov - 17 Nov 2024</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('nutriologo.menus.edit', 2) }}" class="text-green-600 hover:text-green-900"><i class="fas fa-edit mr-1"></i>Editar</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="bg-gray-50 px-4 py-3 text-sm text-gray-600">
            Usa el botón <strong>Nuevo Menú</strong> para crear un plan alimenticio semanal.
        </div>
    </div>
@endsection
