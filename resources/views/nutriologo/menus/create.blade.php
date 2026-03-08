@extends('layouts.app')

@section('title', 'Crear Menú - Nutriólogo')

@section('page-title', 'Crear Nuevo Menú')

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
    <form class="bg-white rounded-lg shadow-md p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Menú</label>
                <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Ej: Menú Semanal Balanceado">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Asignar a Paciente</label>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option>Seleccionar paciente...</option>
                    <option>María González</option>
                    <option>Juan Pérez</option>
                </select>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Plan Semanal</h3>
            <div class="space-y-4">
                @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $day)
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-3">{{ $day }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Desayuno</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ej: Avena con frutas">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Comida</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ej: Pollo con verduras">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Cena</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ej: Sopa de verduras">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('nutriologo.menus.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-save mr-2"></i>Guardar Menú
            </button>
        </div>
    </form>
@endsection

