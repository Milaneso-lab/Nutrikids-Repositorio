@extends('layouts.app')

@section('title', 'Citas - Nutriólogo')

@section('page-title', 'Bandeja de citas')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100 mb-2">Disponibles para tomar</h3>
            <p class="text-sm text-gray-600 dark:text-slate-400 mb-4">Solicitudes sin nutriólogo asignado. Al tomar una, quedará asignada a ti (el administrador también puede asignar desde su panel).</p>
            <div class="space-y-3">
                @forelse($pendientes as $cita)
                    <div class="border border-gray-200 dark:border-slate-700 rounded-lg p-4 bg-gray-50 dark:bg-slate-800/60">
                        <div class="flex justify-between items-start gap-2">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 dark:text-slate-100">
                                    {{ $cita->padre?->nombre }} {{ $cita->padre?->apellido_paterno }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">{{ $cita->padre?->email }}</p>
                                <p class="text-sm text-gray-700 dark:text-slate-300 mt-2">
                                    {{ $cita->fecha_preferida?->format('d/m/Y') }}
                                    · {{ $cita->franja === 'tarde' ? 'Tarde' : 'Mañana' }}
                                </p>
                                @if($cita->telefono)
                                    <p class="text-sm text-gray-600 dark:text-slate-400">Tel: {{ $cita->telefono }}</p>
                                @endif
                                @if($cita->mensaje)
                                    <p class="text-sm text-gray-600 dark:text-slate-300 mt-1">{{ $cita->mensaje }}</p>
                                @endif
                            </div>
                            <form action="{{ route('nutriologo.citas.tomar', $cita) }}" method="POST" class="shrink-0">
                                @csrf
                                <button type="submit" class="whitespace-nowrap bg-green-600 dark:bg-emerald-600 hover:bg-green-700 dark:hover:bg-emerald-500 text-white text-sm font-medium px-3 py-2 rounded-lg">
                                    Tomar cita
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-slate-400 text-sm">No hay citas pendientes sin asignar.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100 mb-4">Mis citas</h3>
            <div class="space-y-3">
                @forelse($mias as $cita)
                    <div class="border border-gray-200 dark:border-slate-700 rounded-lg p-4 bg-white dark:bg-slate-800/40">
                        <div class="flex justify-between items-start gap-2">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 dark:text-slate-100">
                                    {{ $cita->padre?->nombre }} {{ $cita->padre?->apellido_paterno }}
                                </p>
                                <p class="text-sm text-gray-700 dark:text-slate-300">
                                    {{ $cita->fecha_preferida?->format('d/m/Y') }}
                                    · {{ $cita->franja === 'tarde' ? 'Tarde' : 'Mañana' }}
                                </p>
                                <div class="mt-2">
                                    @include('admin.citas._estado-badge', ['estado' => $cita->estado])
                                </div>
                            </div>
                            @if($cita->estado === \App\Models\Cita::ESTADO_ASIGNADA)
                                <form action="{{ route('nutriologo.citas.confirmar', $cita) }}" method="POST" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="text-sm bg-blue-600 dark:bg-sky-600 hover:bg-blue-700 dark:hover:bg-sky-500 text-white px-3 py-2 rounded-lg">
                                        Confirmar
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-slate-400 text-sm">Aún no tienes citas asignadas.</p>
                @endforelse
            </div>
        </div>
    </div>

    @if(isset($proximas) && $proximas->isNotEmpty())
    <div class="mt-6 bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Próximas consultas</h3>
            <a href="{{ route('nutriologo.citas.agenda') }}" class="text-sm text-emerald-600 hover:underline">Ver calendario</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($proximas as $cita)
                <div class="p-4 rounded-lg border dark:border-slate-700 flex justify-between items-start">
                    <div>
                        <p class="font-medium">{{ $cita->padre?->nombre }} {{ $cita->padre?->apellido_paterno }}</p>
                        <p class="text-sm text-slate-500">{{ $cita->fecha_preferida?->format('d/m/Y') }} · {{ $cita->franja === 'tarde' ? 'Tarde' : 'Mañana' }}</p>
                    </div>
                    @include('admin.citas._estado-badge', ['estado' => $cita->estado])
                </div>
            @endforeach
        </div>
    </div>
    @endif
@endsection
