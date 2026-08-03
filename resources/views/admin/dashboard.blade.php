@extends('layouts.app')

@section('title', 'Dashboard - Administrador')

@section('page-title', 'Dashboard Administrador')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stat-card label="Usuarios Totales" :value="$totalUsuarios" icon="fa-users" color="sky" :href="route('admin.usuarios.index')" />
        <x-stat-card label="Nutriólogos" :value="$totalNutriologos" icon="fa-user-md" color="emerald" :href="route('admin.nutriologos.index')" />
        <x-stat-card label="Padres Registrados" :value="$totalPadres" icon="fa-child" color="amber" />
        <x-stat-card label="Pacientes clínicos" :value="$totalPacientes" icon="fa-heart-pulse" color="violet" hint="{{ $totalEvaluaciones }} evaluaciones · {{ $citasConfirmadas }} citas confirmadas" />
    </div>

    @if(!empty($accesosRapidos))
    <div class="mb-8 flex flex-wrap gap-3">
        @foreach($accesosRapidos as $link)
            <a href="{{ $link['route'] }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm font-medium text-slate-700 dark:text-slate-200 hover:border-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300 transition">
                <i class="fas {{ $link['icon'] }}"></i>{{ $link['label'] }}
            </a>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Últimas Actividades -->
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-100 mb-4">Últimos Usuarios Registrados</h3>
            <div class="space-y-4">
                @forelse($ultimosUsuarios as $usuario)
                <div class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-slate-800/80 rounded-lg border border-transparent hover:border-slate-200 dark:hover:border-slate-600 transition-colors">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center
                        @if($usuario->rol === 'admin') bg-purple-100 dark:bg-violet-900/50 text-purple-600 dark:text-violet-300
                        @elseif($usuario->rol === 'nutriologo') bg-green-100 dark:bg-emerald-900/50 text-green-600 dark:text-emerald-300
                        @else bg-yellow-100 dark:bg-amber-900/50 text-yellow-600 dark:text-amber-300
                        @endif">
                        <i class="fas 
                            @if($usuario->rol === 'admin') fa-user-shield
                            @elseif($usuario->rol === 'nutriologo') fa-user-md
                            @else fa-user
                            @endif"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 dark:text-slate-100 truncate">
                            {{ $usuario->nombre }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-slate-400">{{ $usuario->email }}</p>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ring-1 ring-black/5 dark:ring-white/10
                                @if($usuario->rol === 'admin') bg-purple-100 dark:bg-violet-950/60 text-purple-800 dark:text-violet-100
                                @elseif($usuario->rol === 'nutriologo') bg-green-100 dark:bg-emerald-950/60 text-green-800 dark:text-emerald-100
                                @else bg-yellow-100 dark:bg-amber-950/60 text-yellow-900 dark:text-amber-50
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
                <p class="text-gray-500 dark:text-slate-400 text-center py-4">No hay usuarios registrados aún.</p>
                @endforelse
            </div>
        </div>

        <!-- Alertas del Sistema -->
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-100 mb-4">Alertas del Sistema</h3>
            <div class="space-y-4">
                @forelse($alertasSistema as $alerta)
                    @php
                        $alertStyles = match($alerta['tipo']) {
                            'warning' => ['bg' => 'bg-yellow-50 dark:bg-amber-950/35', 'border' => 'border-yellow-500 dark:border-amber-500'],
                            'success' => ['bg' => 'bg-green-50 dark:bg-emerald-950/35', 'border' => 'border-green-500 dark:border-emerald-600'],
                            default => ['bg' => 'bg-blue-50 dark:bg-sky-950/35', 'border' => 'border-blue-500 dark:border-sky-500'],
                        };
                    @endphp
                    <div class="p-4 {{ $alertStyles['bg'] }} border-l-4 {{ $alertStyles['border'] }} rounded-lg">
                        <div class="flex items-center justify-between mb-2 gap-2">
                            <span class="font-semibold text-gray-800 dark:text-slate-100">{{ $alerta['titulo'] }}</span>
                            <span class="text-xs text-gray-600 dark:text-slate-400 shrink-0">{{ $alerta['fecha'] }}</span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-slate-300">{{ $alerta['descripcion'] }}</p>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-slate-400 text-sm text-center py-4">No hay alertas del sistema para mostrar.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection


