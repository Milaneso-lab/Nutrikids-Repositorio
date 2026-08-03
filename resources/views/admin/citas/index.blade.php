@extends('layouts.app')

@section('title', 'Citas - Administrador')

@section('page-title', 'Solicitudes de cita')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
            <p class="text-sm text-gray-600 dark:text-slate-300">Asigna cada solicitud a un nutriólogo o cambia el estado (confirmada / cancelada).</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 border-b border-slate-200 dark:border-slate-600 text-left">
                    <tr>
                        <th class="px-4 py-3 font-semibold">ID</th>
                        <th class="px-4 py-3 font-semibold">Padre</th>
                        <th class="px-4 py-3 font-semibold">Preferencia</th>
                        <th class="px-4 py-3 font-semibold">Teléfono</th>
                        <th class="px-4 py-3 font-semibold">Mensaje</th>
                        <th class="px-4 py-3 font-semibold">Nutriólogo</th>
                        <th class="px-4 py-3 font-semibold">Estado</th>
                        <th class="px-4 py-3 font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($citas as $cita)
                        <tr class="hover:bg-slate-100/90 dark:hover:bg-slate-800/80 text-gray-800 dark:text-slate-200 transition-colors">
                            <td class="px-4 py-3">#{{ $cita->id }}</td>
                            <td class="px-4 py-3">
                                @if($cita->padre)
                                    <span class="font-medium text-gray-900 dark:text-slate-100">{{ $cita->padre->nombre }} {{ $cita->padre->apellido_paterno }}</span>
                                    <div class="text-xs text-gray-500 dark:text-slate-400">{{ $cita->padre->email }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                {{ $cita->fecha_preferida?->format('d/m/Y') ?? '—' }}
                                <div class="text-xs text-gray-500 dark:text-slate-400">{{ $cita->franja === 'tarde' ? 'Tarde' : 'Mañana' }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $cita->telefono ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300 max-w-xs truncate" title="{{ $cita->mensaje }}">{{ $cita->mensaje ?: '—' }}</td>
                            <td class="px-4 py-3">
                                @if($cita->nutriologo)
                                    <span class="text-gray-800 dark:text-slate-100">{{ $cita->nutriologo->nombre }} {{ $cita->nutriologo->apellido_paterno }}</span>
                                @else
                                    <span class="text-amber-600 dark:text-amber-300 text-xs font-medium">Sin asignar</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @include('admin.citas._estado-badge', ['estado' => $cita->estado])
                            </td>
                            <td class="px-4 py-3 space-y-2 align-top min-w-[220px]">
                                @if($nutriologos->isEmpty())
                                    <p class="text-xs text-red-600 dark:text-red-400">Registra al menos un usuario con rol nutriólogo.</p>
                                @else
                                <form action="{{ route('admin.citas.asignar', $cita) }}" method="POST" class="flex flex-col gap-1">
                                    @csrf
                                    <select name="id_nutriologo" class="w-full border border-gray-300 dark:border-slate-600 rounded px-2 py-1 text-xs bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100">
                                        @foreach($nutriologos as $n)
                                            <option value="{{ $n->id_usuario }}" @selected($cita->id_nutriologo == $n->id_usuario)>
                                                {{ $n->nombre }} {{ $n->apellido_paterno }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white text-xs py-1.5 rounded font-medium">
                                        Asignar nutriólogo
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.citas.estado', $cita) }}" method="POST" class="flex flex-wrap gap-1">
                                    @csrf
                                    <select name="estado" class="flex-1 min-w-[100px] border border-gray-300 dark:border-slate-600 rounded px-2 py-1 text-xs bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100">
                                        <option value="pendiente" @selected($cita->estado === 'pendiente')>Pendiente</option>
                                        <option value="asignada" @selected($cita->estado === 'asignada')>Asignada</option>
                                        <option value="confirmada" @selected($cita->estado === 'confirmada')>Confirmada</option>
                                        <option value="cancelada" @selected($cita->estado === 'cancelada')>Cancelada</option>
                                    </select>
                                    <button type="submit" class="bg-gray-700 hover:bg-gray-800 dark:bg-slate-600 dark:hover:bg-slate-500 text-white text-xs px-2 py-1 rounded">Guardar estado</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">No hay solicitudes de cita todavía.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
