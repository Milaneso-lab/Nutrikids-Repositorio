@extends('layouts.app')

@section('title', 'Editar Evaluación - Nutriólogo')

@section('page-title', 'Editar Evaluación Nutricional')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
    <form action="{{ route('nutriologo.evaluaciones.update', $evaluacion) }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Paciente</label>
                <input type="text" readonly value="{{ trim(($evaluacion->paciente->nombre ?? '') . ' ' . ($evaluacion->paciente->apellidos ?? '')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Evaluación</label>
                <input type="text" readonly value="{{ optional($evaluacion->created_at)->format('d/m/Y H:i') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Peso (kg)</label>
                <input type="number" step="0.1" name="peso" value="{{ old('peso', $evaluacion->peso) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                @error('peso')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Talla (cm)</label>
                <input type="number" step="0.1" name="talla" value="{{ old('talla', $evaluacion->talla) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                @error('talla')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">IMC</label>
                <input type="text" readonly value="{{ (is_numeric($evaluacion->peso) && is_numeric($evaluacion->talla) && (float) $evaluacion->talla > 0) ? number_format(((float) $evaluacion->peso) / ((((float) $evaluacion->talla > 3 ? (float) $evaluacion->talla / 100 : (float) $evaluacion->talla)) * (((float) $evaluacion->talla > 3 ? (float) $evaluacion->talla / 100 : (float) $evaluacion->talla))), 2) : 'Sin dato' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
            </div>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Recomendaciones</label>
            <textarea rows="4" name="recomendaciones" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ old('recomendaciones', $evaluacion->recomendaciones) }}</textarea>
            @error('recomendaciones')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex justify-end space-x-3">
            <a href="{{ route('nutriologo.evaluaciones.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"><i class="fas fa-save mr-2"></i>Guardar cambios</button>
        </div>
    </form>
@endsection

