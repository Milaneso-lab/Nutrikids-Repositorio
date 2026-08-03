@extends('layouts.app')

@section('title', 'Crear Nutriólogo - Administrador')

@section('page-title', 'Crear Nutriólogo')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <x-page-header title="Nueva cuenta de nutriólogo" subtitle="Datos mínimos para acceso al panel clínico." />

    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800 max-w-2xl">
        <form action="{{ route('admin.nutriologos.store') }}" method="POST" class="space-y-4">
            @csrf
            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-800 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Apellido paterno</label>
                    <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-800 dark:text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Apellido materno</label>
                    <input type="text" name="apellido_materno" value="{{ old('apellido_materno') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-800 dark:text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-800 dark:text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Contraseña</label>
                    <input type="password" name="contrasena" required class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-800 dark:text-slate-100">
                    <p class="text-xs text-gray-500 dark:text-slate-500 mt-1">Mínimo 8 caracteres, mayúscula, minúscula y número.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('admin.nutriologos.index') }}" class="px-4 py-2 bg-slate-600 text-white rounded-lg text-sm">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm">Crear nutriólogo</button>
            </div>
        </form>
    </div>
@endsection
