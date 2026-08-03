@extends('layouts.app')

@section('title', 'Editar Nutriólogo - Administrador')

@section('page-title', 'Editar Nutriólogo')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <x-page-header title="Editar nutriólogo" :subtitle="$nutriologo->email" />

    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800 max-w-2xl">
        <form action="{{ route('admin.nutriologos.update', $nutriologo->id_usuario) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $nutriologo->nombre) }}" required class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-800 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Apellido paterno</label>
                    <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno', $nutriologo->apellido_paterno) }}" required class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-800 dark:text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Apellido materno</label>
                    <input type="text" name="apellido_materno" value="{{ old('apellido_materno', $nutriologo->apellido_materno) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-800 dark:text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email', $nutriologo->email) }}" required class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-800 dark:text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nueva contraseña (opcional)</label>
                    <input type="password" name="contrasena" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-800 dark:text-slate-100">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('admin.nutriologos.index') }}" class="px-4 py-2 bg-slate-600 text-white rounded-lg text-sm">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm">Guardar cambios</button>
            </div>
        </form>
    </div>
@endsection
