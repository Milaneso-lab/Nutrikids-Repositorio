@extends('layouts.app')

@section('title', 'Crear Usuario - Administrador')

@section('page-title', 'Crear Nuevo Usuario')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800 text-gray-900 dark:text-slate-100">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-100">Crear Nuevo Usuario</h3>
            <a href="{{ route('admin.usuarios.index') }}" class="text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white">
                <i class="fas fa-arrow-left mr-2"></i>Volver a Usuarios
            </a>
        </div>

        <form action="{{ route('admin.usuarios.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Nombre</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Apellido Paterno</label>
                <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Apellido Materno (Opcional)</label>
                <input type="text" name="apellido_materno" value="{{ old('apellido_materno') }}" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Contraseña</label>
                <input type="password" name="contrasena" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Rol</label>
                <select name="rol" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
                    <option value="admin" {{ old('rol') === 'admin' ? 'selected' : '' }}>Administrador</option>
                    <option value="nutriologo" {{ old('rol') === 'nutriologo' ? 'selected' : '' }}>Nutriólogo</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="bg-green-600 dark:bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 dark:hover:bg-emerald-500 transition">
                    <i class="fas fa-save mr-2"></i>Crear Usuario
                </button>
            </div>
        </form>
    </div>
@endsection