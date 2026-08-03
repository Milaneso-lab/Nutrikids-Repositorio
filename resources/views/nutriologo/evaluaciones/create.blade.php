@extends('layouts.app')

@section('title', 'Nueva Evaluación - Nutriólogo')

@section('page-title', 'Nueva Evaluación Nutricional')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
    <form action="{{ route('nutriologo.evaluaciones.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Paciente</label>
                <select name="paciente_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                    <option value="">Seleccionar paciente...</option>
                    @foreach($pacientes as $paciente)
                        <option value="{{ $paciente->id }}" @selected(old('paciente_id', $selectedPacienteId ?? null) == $paciente->id)>
                            {{ trim($paciente->nombre . ' ' . $paciente->apellidos) }}
                        </option>
                    @endforeach
                </select>
                @if($pacientes->isEmpty())
                    <p class="mt-2 text-sm text-amber-700">No hay pacientes registrados todavía. Primero crea un paciente para poder registrar su evaluación.</p>
                @endif
                @error('paciente_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Registro</label>
                <input type="text" value="La fecha se guardará automáticamente" readonly class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Peso (kg)</label>
                <input type="number" step="0.1" name="peso" value="{{ old('peso') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                @error('peso')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Talla (cm)</label>
                <input type="number" step="0.1" name="talla" value="{{ old('talla') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                @error('talla')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">IMC estimado</label>
                <input type="text" readonly value="Se calcula al guardar" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Recomendaciones</label>
            <textarea rows="6" name="recomendaciones" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Ingrese las recomendaciones nutricionales...">{{ old('recomendaciones') }}</textarea>
            @error('recomendaciones')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('nutriologo.evaluaciones.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" @disabled($pacientes->isEmpty())>
                <i class="fas fa-save mr-2"></i>Guardar Evaluación
            </button>
        </div>
    </form>
@endsection


