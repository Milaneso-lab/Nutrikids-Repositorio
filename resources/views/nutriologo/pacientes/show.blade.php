@extends('layouts.app')

@section('title', 'Perfil del Paciente - Nutriólogo')

@section('page-title', 'Perfil del Paciente')

@section('navigation')
    <a href="{{ route('nutriologo.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('nutriologo.pacientes.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-green-500 rounded-lg text-white">
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
    <a href="{{ route('nutriologo.reportes.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-chart-bar"></i>
        <span>Reportes</span>
    </a>
@endsection

@section('content')
    <!-- Información del Paciente -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-child text-green-600 text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">María González</h2>
                    <p class="text-gray-600">8 años • Femenino</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('nutriologo.pacientes.edit', $id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-edit mr-2"></i>Editar
                </a>
                <a href="{{ route('nutriologo.evaluaciones.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-plus mr-2"></i>Nueva Evaluación
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Peso Actual</p>
                <p class="text-2xl font-bold text-gray-800">28.5 kg</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Talla</p>
                <p class="text-2xl font-bold text-gray-800">124 cm</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">IMC</p>
                <p class="text-2xl font-bold text-gray-800">18.5</p>
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs mt-2 inline-block">Normal</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Gráfica de Crecimiento -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Evolución de Peso y Talla</h3>
            <canvas id="growthChart" height="250"></canvas>
        </div>

        <!-- Gráfica de IMC -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Evolución de IMC</h3>
            <canvas id="imcChart" height="250"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Alergias -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Alergias Registradas</h3>
            <div class="space-y-2">
                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                        <span class="font-medium text-gray-800">Lactosa</span>
                    </div>
                    <span class="text-sm text-gray-600">Severa</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-exclamation-circle text-yellow-600"></i>
                        <span class="font-medium text-gray-800">Nueces</span>
                    </div>
                    <span class="text-sm text-gray-600">Moderada</span>
                </div>
            </div>
            <button class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 w-full">
                <i class="fas fa-plus mr-2"></i>Agregar Alergia
            </button>
        </div>

        <!-- Historial de Menús -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Menús Asignados</h3>
            <div class="space-y-3">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-800">Menú Semanal #12</span>
                        <span class="text-sm text-gray-600">15 Nov 2024</span>
                    </div>
                    <p class="text-sm text-gray-600">Menú balanceado para crecimiento saludable</p>
                    <a href="#" class="text-green-600 text-sm mt-2 inline-block">Ver detalles →</a>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-800">Menú Semanal #11</span>
                        <span class="text-sm text-gray-600">8 Nov 2024</span>
                    </div>
                    <p class="text-sm text-gray-600">Menú con restricción de lactosa</p>
                    <a href="#" class="text-green-600 text-sm mt-2 inline-block">Ver detalles →</a>
                </div>
            </div>
            <a href="{{ route('nutriologo.menus.create') }}" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 w-full block text-center">
                <i class="fas fa-plus mr-2"></i>Asignar Nuevo Menú
            </a>
        </div>
    </div>

    <!-- Notas del Nutriólogo -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Notas del Nutriólogo</h3>
        <div class="space-y-4">
            <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-gray-800">Nota del 15 de Noviembre</span>
                    <span class="text-sm text-gray-600">Dr. Sandra Olmos</span>
                </div>
                <p class="text-gray-700">Paciente muestra mejoría en hábitos alimenticios. Se recomienda continuar con el plan actual y aumentar la ingesta de frutas.</p>
            </div>
            <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-gray-800">Nota del 8 de Noviembre</span>
                    <span class="text-sm text-gray-600">Dr. Sandra Olmos</span>
                </div>
                <p class="text-gray-700">Primera evaluación completada. IMC dentro de parámetros normales. Se asignó menú inicial.</p>
            </div>
        </div>
        <button class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>Agregar Nota
        </button>
    </div>
@endsection

@push('scripts')
<script>
    // Gráfica de crecimiento
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov'],
            datasets: [{
                label: 'Peso (kg)',
                data: [25, 25.5, 26, 26.5, 27, 27.2, 27.8, 28, 28.2, 28.3, 28.5],
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                yAxisID: 'y',
            }, {
                label: 'Talla (cm)',
                data: [118, 119, 120, 121, 122, 122.5, 123, 123.5, 123.8, 124, 124],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Gráfica de IMC
    const imcCtx = document.getElementById('imcChart').getContext('2d');
    new Chart(imcCtx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov'],
            datasets: [{
                label: 'IMC',
                data: [17.9, 18.0, 18.0, 18.1, 18.1, 18.2, 18.3, 18.4, 18.4, 18.5, 18.5],
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    min: 15,
                    max: 25
                }
            }
        }
    });
</script>
@endpush

