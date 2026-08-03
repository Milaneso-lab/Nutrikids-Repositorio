@extends('layouts.app')

@section('title', 'Nuevo Paciente - Nutriólogo')

@section('page-title', 'Registrar Nuevo Paciente')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('nutriologo.pacientes.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del niño/a</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Ej: María" required>
                    @error('nombre')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Apellidos</label>
                    <input type="text" name="apellidos" value="{{ old('apellidos') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Ej: González López">
                    @error('apellidos')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" max="{{ now()->format('Y-m-d') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                    @error('fecha_nacimiento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Vincular con un niño registrado en la app
                    </label>
                    <select name="nino_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">Sin vincular</option>
                        @foreach ($ninos as $nino)
                            <option value="{{ $nino->id }}" @selected(old('nino_id') == $nino->id)>
                                {{ $nino->nombre_completo }} — nacido el {{ $nino->fecha_nacimiento?->format('d/m/Y') }}
                            </option>
                        @endforeach
                    </select>
                    @error('nino_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">
                        Al vincularlo, las mediciones y los planes que registres aquí se verán en la
                        aplicación móvil del padre. Sólo aparecen los niños que aún no tienen expediente.
                    </p>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('nutriologo.pacientes.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-save mr-2"></i>Guardar Paciente
                </button>
            </div>
        </form>
    </div>
@endsection

