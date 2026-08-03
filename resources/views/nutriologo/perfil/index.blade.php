@extends('layouts.app')

@section('title', 'Mi perfil - Nutriólogo')

@section('page-title', 'Perfil profesional')

@section('navigation')
    @include('nutriologo.partials.navigation')
@endsection

@section('content')
<div class="max-w-3xl">
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-slate-200/80 dark:border-slate-800 p-6">
        <form action="{{ route('nutriologo.perfil.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="flex items-center gap-6">
                <div class="w-24 h-24 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center overflow-hidden shrink-0">
                    @if($usuario->foto_path)
                        <img src="{{ asset('storage/'.$usuario->foto_path) }}" alt="Foto" class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-user-md text-3xl text-emerald-600"></i>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Fotografía</label>
                    <input type="file" name="foto" accept="image/*" class="text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="text-sm font-medium">Nombre</label><input name="nombre" value="{{ old('nombre', $usuario->nombre) }}" class="w-full mt-1 px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600" required></div>
                <div><label class="text-sm font-medium">Apellido paterno</label><input name="apellido_paterno" value="{{ old('apellido_paterno', $usuario->apellido_paterno) }}" class="w-full mt-1 px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600" required></div>
                <div><label class="text-sm font-medium">Apellido materno</label><input name="apellido_materno" value="{{ old('apellido_materno', $usuario->apellido_materno) }}" class="w-full mt-1 px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600"></div>
                <div><label class="text-sm font-medium">Email</label><input type="email" name="email" value="{{ old('email', $usuario->email) }}" class="w-full mt-1 px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600" required></div>
                <div><label class="text-sm font-medium">Teléfono</label><input name="telefono" value="{{ old('telefono', $usuario->telefono) }}" class="w-full mt-1 px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600"></div>
                <div><label class="text-sm font-medium">Especialidad</label><input name="especialidad" value="{{ old('especialidad', $usuario->especialidad) }}" placeholder="Nutrición pediátrica" class="w-full mt-1 px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600"></div>
                <div class="md:col-span-2"><label class="text-sm font-medium">Disponibilidad</label><input name="disponibilidad" value="{{ old('disponibilidad', $usuario->disponibilidad) }}" placeholder="Lun-Vie 9:00-14:00" class="w-full mt-1 px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600"></div>
                <div class="md:col-span-2"><label class="text-sm font-medium">Nueva contraseña (opcional)</label><input type="password" name="contrasena" class="w-full mt-1 px-3 py-2 border rounded-lg dark:bg-slate-800 dark:border-slate-600" minlength="8"></div>
            </div>
            <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Guardar perfil</button>
        </form>
    </div>
</div>
@endsection
