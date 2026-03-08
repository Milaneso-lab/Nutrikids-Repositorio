@extends('layouts.app')

@section('title', 'Dashboard - Nutriólogo')

@section('page-title', 'Dashboard')

@section('navigation')
    <a href="{{ route('nutriologo.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 bg-green-500 rounded-lg text-white">
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
    <a href="{{ route('nutriologo.reportes.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-chart-bar"></i>
        <span>Reportes</span>
    </a>
@endsection

@section('content')
    <!-- Métricas Rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Pacientes Activos</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">24</p>
                </div>
                <div class="bg-green-100 p-4 rounded-full">
                    <i class="fas fa-child text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Próximas Citas</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">8</p>
                </div>
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-calendar-check text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">IMC Promedio</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">18.5</p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-full">
                    <i class="fas fa-weight text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Alertas Pendientes</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">3</p>
                </div>
                <div class="bg-red-100 p-4 rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Próximas Citas -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Próximas Citas</h3>
                <a href="{{ route('nutriologo.pacientes.index') }}" class="text-green-600 hover:text-green-700 text-sm">Ver todas</a>
            </div>
            <div class="space-y-4">
                <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-green-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">María González</p>
                        <p class="text-sm text-gray-600">Hoy, 10:00 AM</p>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs">Confirmada</span>
                </div>
                <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">Juan Pérez</p>
                        <p class="text-sm text-gray-600">Mañana, 2:00 PM</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">Pendiente</span>
                </div>
                <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-yellow-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">Ana Martínez</p>
                        <p class="text-sm text-gray-600">15 Nov, 11:00 AM</p>
                    </div>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">Por confirmar</span>
                </div>
            </div>
        </div>

        <!-- Alertas y Notificaciones -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Alertas</h3>
                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs">3 nuevas</span>
            </div>
            <div class="space-y-4">
                <div class="flex items-start space-x-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <i class="fas fa-exclamation-circle text-red-600 mt-1"></i>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">IMC bajo detectado</p>
                        <p class="text-sm text-gray-600">Paciente: Carlos Ramírez - IMC: 14.2</p>
                        <p class="text-xs text-gray-500 mt-1">Hace 2 horas</p>
                    </div>
                </div>
                <div class="flex items-start space-x-4 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg">
                    <i class="fas fa-clock text-yellow-600 mt-1"></i>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">Evaluación pendiente</p>
                        <p class="text-sm text-gray-600">Paciente: Sofía López - Evaluación atrasada 3 días</p>
                        <p class="text-xs text-gray-500 mt-1">Hace 5 horas</p>
                    </div>
                </div>
                <div class="flex items-start space-x-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
                    <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">Nuevo paciente asignado</p>
                        <p class="text-sm text-gray-600">Paciente: Diego Torres</p>
                        <p class="text-xs text-gray-500 mt-1">Ayer</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfica de Progreso -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Evolución de IMC - Últimos 6 meses</h3>
        <canvas id="imcChart" height="80"></canvas>
    </div>
@endsection

@push('scripts')
<script>
    const ctx = document.getElementById('imcChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre'],
            datasets: [{
                label: 'IMC Promedio',
                data: [17.8, 18.1, 18.3, 18.2, 18.5, 18.5],
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
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

