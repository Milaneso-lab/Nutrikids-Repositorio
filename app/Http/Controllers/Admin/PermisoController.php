<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use App\Models\Role;
use App\Services\Rbac\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PermisoController extends Controller
{
    public function index(PermissionService $permissions)
    {
        $matrix = $permissions->rolesWithPermissions();
        $allPermissions = $permissions->allPermissions();
        $canEdit = Schema::hasTable('rol_permiso');

        return view('admin.permisos.index', compact('matrix', 'allPermissions', 'canEdit'));
    }

    public function sync(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permisos' => 'nullable|array',
            'permisos.*' => 'string|max:100',
        ]);

        if (! Schema::hasTable('permisos')) {
            return back()->with('error', 'No se pudo actualizar los permisos. Inténtalo de nuevo.');
        }

        $ids = Permiso::whereIn('clave', $validated['permisos'] ?? [])->pluck('id');
        $role->permisos()->sync($ids);

        return back()->with('success', 'Permisos del rol actualizados.');
    }
}
