@extends('layouts.app')

@section('title', 'Roles - Admin')

@section('page-title', 'Gestión de roles')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
<div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border p-6">
    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">Roles del sistema y permisos asignados. Los roles base se sincronizan con el seeder RBAC.</p>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-100 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-2 text-left">Rol</th>
                    <th class="px-4 py-2 text-left">Descripción</th>
                    <th class="px-4 py-2 text-left">Permisos</th>
                    @if($dbRoles->isNotEmpty())<th class="px-4 py-2 text-left">Editar</th>@endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @foreach($roles as $role)
                    <tr>
                        <td class="px-4 py-3 font-medium capitalize">{{ $role['nombre'] }}</td>
                        <td class="px-4 py-3">{{ $role['descripcion'] ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse($role['permisos'] as $p)
                                    <span class="px-2 py-0.5 text-xs rounded bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200">{{ $p }}</span>
                                @empty
                                    <span class="text-slate-500">Sin permisos</span>
                                @endforelse
                            </div>
                        </td>
                        @if($dbRoles->isNotEmpty())
                            @php $dbRole = $dbRoles->firstWhere('nombre', $role['nombre']); @endphp
                            <td class="px-4 py-3">
                                @if($dbRole)
                                    <form action="{{ route('admin.roles.update', $dbRole) }}" method="POST" class="flex gap-2">
                                        @csrf @method('PUT')
                                        <input type="text" name="descripcion" value="{{ $dbRole->descripcion }}" class="px-2 py-1 border rounded text-xs dark:bg-slate-800 dark:border-slate-600">
                                        <button type="submit" class="text-xs text-emerald-600 hover:underline">Guardar</button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="mt-4 text-xs text-slate-500"><a href="{{ route('admin.permisos.index') }}" class="text-emerald-600 hover:underline">Gestionar matriz de permisos →</a></p>
</div>
@endsection
