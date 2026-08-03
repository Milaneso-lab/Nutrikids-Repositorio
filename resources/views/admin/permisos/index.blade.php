@extends('layouts.app')

@section('title', 'Permisos - Admin')

@section('page-title', 'Gestión de permisos')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
<div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border p-6 mb-6">
    <h3 class="font-semibold mb-4">Catálogo de permisos</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
        @foreach($allPermissions as $perm)
            <div class="p-2 rounded border dark:border-slate-700">
                <code class="text-emerald-700 dark:text-emerald-300">{{ $perm['clave'] }}</code>
                <p class="text-slate-500 text-xs mt-1">{{ $perm['descripcion'] }}</p>
            </div>
        @endforeach
    </div>
</div>

<div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border p-6">
    <h3 class="font-semibold mb-4">Matriz rol → permisos</h3>
    @if(!$canEdit)
        <p class="text-sm text-amber-700 dark:text-amber-300 mb-4">La edición de permisos no está disponible en este momento. Contacta al administrador del sistema.</p>
    @endif
    @foreach($matrix as $role)
        <div class="mb-6 border-b dark:border-slate-700 pb-4">
            <h4 class="font-medium capitalize mb-2">{{ $role['nombre'] }}</h4>
            @if($canEdit && ($dbRole = \App\Models\Role::where('nombre', $role['nombre'])->first()))
                <form action="{{ route('admin.permisos.sync', $dbRole) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
                        @foreach($allPermissions as $perm)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="permisos[]" value="{{ $perm['clave'] }}"
                                    @checked(in_array($perm['clave'], $role['permisos'], true))>
                                <span>{{ $perm['clave'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    <button type="submit" class="mt-3 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Guardar permisos</button>
                </form>
            @else
                <div class="flex flex-wrap gap-1">
                    @foreach($role['permisos'] as $p)
                        <span class="px-2 py-0.5 text-xs rounded bg-slate-100 dark:bg-slate-800">{{ $p }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
@endsection
