@extends('layouts.app')

@section('title', 'Ver Reporte - Nutriólogo')

@section('page-title', 'Detalle del Reporte')

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
    <div class="mb-4">
        <a href="{{ route('nutriologo.reportes.index') }}" class="text-green-600 hover:text-green-700 text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Volver a reportes
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <h3 class="text-xl font-semibold text-gray-800">Reporte de progreso nutricional</h3>
            <p class="text-sm text-gray-600 mt-1">Resumen de evaluaciones y evolución del paciente.</p>
        </div>
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Paciente</p>
                    <p class="font-semibold text-gray-800">María González</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Período</p>
                    <p class="font-semibold text-gray-800">Noviembre 2024</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">IMC promedio</p>
                    <p class="font-semibold text-gray-800">18.5</p>
                </div>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800 mb-3">Evolución de peso y talla</h4>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Peso (kg)</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Talla (cm)</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">IMC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr><td class="px-4 py-3 text-sm">15 Nov 2024</td><td class="px-4 py-3 text-sm">28.5</td><td class="px-4 py-3 text-sm">124</td><td class="px-4 py-3 text-sm">18.5</td></tr>
                            <tr><td class="px-4 py-3 text-sm">01 Nov 2024</td><td class="px-4 py-3 text-sm">28.2</td><td class="px-4 py-3 text-sm">123</td><td class="px-4 py-3 text-sm">18.6</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                    <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                </button>
            </div>
        </div>
    </div>
@endsection
