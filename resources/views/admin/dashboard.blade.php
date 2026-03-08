@extends('layouts.app')

@section('title', 'Dashboard - Administrador')

@section('page-title', 'Dashboard Administrador')

@section('navigation')
    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 bg-green-500 rounded-lg text-white">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('admin.usuarios.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-users"></i>
        <span>Usuarios</span>
    </a>
    <a href="{{ route('admin.contenido.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-database"></i>
        <span>Contenido</span>
    </a>
    <a href="{{ route('admin.configuracion.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-cog"></i>
        <span>Configuración</span>
    </a>
@endsection

@section('content')

    <!-- Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Usuarios Totales</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalUsuarios }}</p>
                </div>
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Nutriólogos</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalNutriologos }}</p>
                </div>
                <div class="bg-green-100 p-4 rounded-full">
                    <i class="fas fa-user-md text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Padres Registrados</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalPadres }}</p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-full">
                    <i class="fas fa-child text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Administradores</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalAdmins }}</p>
                </div>
                <div class="bg-purple-100 p-4 rounded-full">
                    <i class="fas fa-user-shield text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Últimas Actividades -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Últimos Usuarios Registrados</h3>
            <div class="space-y-4">
                @forelse($ultimosUsuarios as $usuario)
                <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center
                        @if($usuario->rol === 'admin') bg-purple-100 text-purple-600
                        @elseif($usuario->rol === 'nutriologo') bg-green-100 text-green-600
                        @else bg-yellow-100 text-yellow-600
                        @endif">
                        <i class="fas 
                            @if($usuario->rol === 'admin') fa-user-shield
                            @elseif($usuario->rol === 'nutriologo') fa-user-md
                            @else fa-user
                            @endif"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">
                            {{ $usuario->nombre }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}
                        </p>
                        <p class="text-sm text-gray-600">{{ $usuario->email }}</p>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($usuario->rol === 'admin') bg-purple-100 text-purple-800
                                @elseif($usuario->rol === 'nutriologo') bg-green-100 text-green-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                @if($usuario->rol === 'admin') Administrador
                                @elseif($usuario->rol === 'nutriologo') Nutriólogo
                                @else Padre
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No hay usuarios registrados aún.</p>
                @endforelse
            </div>
        </div>

        <!-- Alertas del Sistema -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Alertas del Sistema</h3>
            <div class="space-y-4">
                <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-800">Backup pendiente</span>
                        <span class="text-xs text-gray-600">Hace 2 días</span>
                    </div>
                    <p class="text-sm text-gray-600">Se recomienda realizar backup de la base de datos</p>
                </div>
                <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-800">Sistema operativo</span>
                        <span class="text-xs text-gray-600">Ahora</span>
                    </div>
                    <p class="text-sm text-gray-600">Todos los servicios funcionando correctamente</p>
                </div>
            </div>
        </div>
    </div>
@endsection


