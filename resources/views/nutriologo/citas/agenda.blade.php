@extends('layouts.app')

@section('title', 'Agenda - Nutriólogo')

@section('page-title', 'Agenda clínica')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
@php
    $inicio = $month->copy()->startOfMonth();
    $fin = $month->copy()->endOfMonth();
    $startPad = ($inicio->dayOfWeek + 6) % 7;
    $daysInMonth = $inicio->daysInMonth;
@endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 bg-white dark:bg-slate-900 rounded-lg shadow-md border border-slate-200/80 dark:border-slate-800 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold capitalize">{{ $month->translatedFormat('F Y') }}</h3>
            <div class="flex gap-2">
                <a href="{{ route('nutriologo.citas.agenda', ['mes' => $month->copy()->subMonth()->format('Y-m')]) }}" class="px-3 py-1 rounded bg-slate-100 dark:bg-slate-800 text-sm"><i class="fas fa-chevron-left"></i></a>
                <a href="{{ route('nutriologo.citas.agenda', ['mes' => $month->copy()->addMonth()->format('Y-m')]) }}" class="px-3 py-1 rounded bg-slate-100 dark:bg-slate-800 text-sm"><i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
        <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-slate-500 mb-2">
            @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d)<div>{{ $d }}</div>@endforeach
        </div>
        <div class="grid grid-cols-7 gap-1">
            @for($i = 0; $i < $startPad; $i++)<div class="h-16"></div>@endfor
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateKey = $inicio->copy()->day($day)->format('Y-m-d');
                    $citasDia = $citasMes->get($dateKey, collect());
                    $isToday = $dateKey === today()->format('Y-m-d');
                @endphp
                <div class="h-16 p-1 rounded-lg border {{ $isToday ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/30' : 'border-slate-200 dark:border-slate-700' }}">
                    <span class="text-xs font-medium {{ $isToday ? 'text-emerald-700 dark:text-emerald-300' : '' }}">{{ $day }}</span>
                    @if($citasDia->count())
                        <div class="mt-1 space-y-0.5">
                            @foreach($citasDia->take(2) as $c)
                                <div class="text-[10px] truncate px-1 rounded bg-sky-100 dark:bg-sky-900/50 text-sky-800 dark:text-sky-200" title="{{ $c->padre?->nombre }}">{{ $c->franja === 'tarde' ? 'T' : 'M' }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endfor
        </div>
        <p class="mt-4 text-xs text-slate-500">M = mañana · T = tarde. <a href="{{ route('nutriologo.citas.index') }}" class="text-emerald-600 hover:underline">Ir a bandeja de citas</a></p>
    </div>

    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-slate-200/80 dark:border-slate-800 p-6">
            <h3 class="font-semibold mb-4"><i class="fas fa-bell text-amber-500 mr-2"></i>Recordatorios</h3>
            <div class="space-y-3">
                @forelse($recordatorios as $cita)
                    <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                        <p class="text-sm font-medium">{{ $cita->padre?->nombre }} {{ $cita->padre?->apellido_paterno }}</p>
                        <p class="text-xs text-slate-500">{{ $cita->fecha_preferida?->format('d/m/Y') }} · {{ $cita->franja === 'tarde' ? 'Tarde' : 'Mañana' }}</p>
                        @include('admin.citas._estado-badge', ['estado' => $cita->estado])
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No hay consultas próximas programadas.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
