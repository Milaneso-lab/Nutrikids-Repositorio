@extends('layouts.app')

@section('title', 'Nuevo Paciente - Nutriólogo')

@section('page-title', 'Registrar Nuevo Paciente')

@section('navigation')
    <a href="{{ route('nutriologo.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('nutriologo.pacientes.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-green-500 rounded-lg text-white">
        <i class="fas fa-child"></i>
        <span>Pacientes</span>
    </a>
    <a href="{{ route('nutriologo.evaluaciones.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-clipboard-check"></i>
        <span>Evaluaciones</span>
    </a>
    <a href="{{ route('nutriologo.menus.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-utensils"></i>
        <span>Menús</span>
    </a>
    <a href="{{ route('nutriologo.reportes.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-chart-bar"></i>
        <span>Reportes</span>
    </a>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="#" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del niño/a</label>
                    <input type="text" name="nombre" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Ej: María" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Apellidos</label>
                    <input type="text" name="apellidos" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Ej: González López">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sexo</label>
                    <select name="sexo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">Seleccionar...</option>
                        <option value="F">Femenino</option>
                        <option value="M">Masculino</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del tutor o responsable</label>
                    <input type="text" name="nombre_tutor" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Ej: Carlos González">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Correo del tutor</label>
                    <input type="email" name="email_tutor" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="tutor@email.com">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono de contacto</label>
                    <input type="tel" name="telefono" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Ej: 55 1234 5678">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones o notas</label>
                    <textarea name="observaciones" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Alergias, condiciones especiales, etc."></textarea>
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
