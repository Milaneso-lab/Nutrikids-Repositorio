@extends('layouts.app')

@section('title', 'Reportes - Nutriólogo')

@section('page-title', 'Reportes')

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
    <a href="{{ route('nutriologo.menus.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-utensils"></i>
        <span>Menús</span>
    </a>
    <a href="{{ route('nutriologo.reportes.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-green-500 rounded-lg text-white">
        <i class="fas fa-chart-bar"></i>
        <span>Reportes</span>
    </a>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Reportes disponibles</h3>
            <p class="text-sm text-gray-600 mt-1">Consulta reportes por paciente o genera nuevos.</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="border border-gray-200 rounded-lg p-6 hover:border-green-500 hover:shadow-md transition">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-file-alt text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Reporte de progreso</h4>
                            <p class="text-sm text-gray-600">María González</p>
                            <p class="text-xs text-gray-500">Nov 2024</p>
                        </div>
                    </div>
                    <a href="{{ route('nutriologo.reportes.show', 1) }}" class="mt-4 inline-flex items-center text-green-600 hover:text-green-700 text-sm font-medium">
                        Ver reporte <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="border border-gray-200 rounded-lg p-6 hover:border-green-500 hover:shadow-md transition">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Reporte de progreso</h4>
                            <p class="text-sm text-gray-600">Juan Pérez</p>
                            <p class="text-xs text-gray-500">Nov 2024</p>
                        </div>
                    </div>
                    <a href="{{ route('nutriologo.reportes.show', 2) }}" class="mt-4 inline-flex items-center text-green-600 hover:text-green-700 text-sm font-medium">
                        Ver reporte <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="border border-gray-200 rounded-lg p-6 border-dashed hover:border-green-400 hover:bg-green-50/50 transition">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-plus text-gray-400 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-600">Generar nuevo reporte</h4>
                            <p class="text-sm text-gray-500">Selecciona paciente y período</p>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">Próximamente podrás generar reportes desde aquí.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
