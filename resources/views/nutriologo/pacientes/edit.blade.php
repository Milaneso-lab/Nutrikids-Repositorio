@extends('layouts.app')

@section('title', 'Editar Paciente - Nutriólogo')

@section('page-title', 'Editar expediente')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
        <form action="{{ route('nutriologo.pacientes.update', $paciente) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <fieldset class="space-y-4">
                <legend class="text-lg font-semibold text-gray-800 dark:text-slate-100">Datos generales</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Nombre</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $paciente->nombre) }}" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-800 rounded-lg" required>
                        @error('nombre')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Apellidos</label>
                        <input type="text" name="apellidos" value="{{ old('apellidos', $paciente->apellidos) }}" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-800 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Fecha de nacimiento</label>
                        <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', optional($paciente->fecha_nacimiento)->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-800 rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Estado del paciente</label>
                        <select name="estado_paciente" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-800 rounded-lg">
                            @foreach(['activo','seguimiento','inactivo','alta'] as $st)
                                <option value="{{ $st }}" @selected(old('estado_paciente', $paciente->estado_paciente ?? 'activo') === $st)>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                            Vincular con un niño registrado en la app
                        </label>
                        <select name="nino_id" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-800 rounded-lg">
                            <option value="">Sin vincular</option>
                            @foreach ($ninos as $nino)
                                <option value="{{ $nino->id }}" @selected(old('nino_id', $paciente->nino_id) == $nino->id)>
                                    {{ $nino->nombre_completo }} — nacido el {{ $nino->fecha_nacimiento?->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                        @error('nino_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-2 text-sm text-gray-500 dark:text-slate-400">
                            Al vincularlo, las mediciones y los planes de este expediente se verán en la
                            aplicación móvil del padre.
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Objetivo nutricional</label>
                        <input type="text" name="objetivo_nutricional" value="{{ old('objetivo_nutricional', $paciente->objetivo_nutricional) }}" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-800 rounded-lg" placeholder="Ej. Normalizar IMC en 6 meses">
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="text-lg font-semibold text-gray-800 dark:text-slate-100">Historia clínica</legend>
                <textarea name="historia_clinica" rows="4" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-800 rounded-lg">{{ old('historia_clinica', $paciente->historia_clinica) }}</textarea>
                <textarea name="antecedentes" rows="3" placeholder="Antecedentes familiares y personales" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-800 rounded-lg">{{ old('antecedentes', $paciente->antecedentes) }}</textarea>
                <textarea name="alergias" rows="2" placeholder="Alergias e intolerancias" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-800 rounded-lg">{{ old('alergias', $paciente->alergias) }}</textarea>
                <textarea name="notas_seguimiento" rows="3" placeholder="Notas de seguimiento clínico" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-800 rounded-lg">{{ old('notas_seguimiento', $paciente->notas_seguimiento) }}</textarea>
            </fieldset>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('nutriologo.pacientes.show', $paciente) }}" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"><i class="fas fa-save mr-2"></i>Guardar expediente</button>
            </div>
        </form>
    </div>
@endsection
