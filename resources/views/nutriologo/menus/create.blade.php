@extends('layouts.app')

@section('title', 'Crear Menú - Nutriólogo')

@section('page-title', 'Crear Nuevo Menú')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
    <form action="{{ route('nutriologo.menus.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Menú</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Ej: Menú Semanal Balanceado" required>
                @error('nombre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Asignar a Paciente</label>
                <select name="paciente_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                    <option value="">Seleccionar paciente...</option>
                    @foreach($pacientes as $paciente)
                        <option value="{{ $paciente->id }}" @selected(old('paciente_id', $selectedPacienteId ?? null) == $paciente->id)>
                            {{ trim($paciente->nombre . ' ' . $paciente->apellidos) }}
                        </option>
                    @endforeach
                </select>
                @if($pacientes->isEmpty())
                    <p class="mt-2 text-sm text-amber-700">No hay pacientes registrados todavía. Primero crea un paciente para poder asignarle un menú.</p>
                @endif
                @error('paciente_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado inicial</label>
                <select name="estado" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="activo" @selected(old('estado', 'activo') === 'activo')>Activo</option>
                    <option value="borrador" @selected(old('estado') === 'borrador')>Borrador</option>
                </select>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Descripción del plan alimenticio</label>
            <textarea name="descripcion" rows="10" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Escribe aquí el plan semanal, por ejemplo:&#10;Lunes - Desayuno: ...&#10;Lunes - Comida: ...&#10;Lunes - Cena: ...">{{ old('descripcion') }}</textarea>
            <p class="mt-2 text-sm text-gray-500">La base actual del proyecto guarda el plan completo en un solo campo de descripción.</p>
            @error('descripcion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('nutriologo.menus.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" @disabled($pacientes->isEmpty())>
                <i class="fas fa-save mr-2"></i>Guardar Menú
            </button>
        </div>
    </form>
@endsection


