<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespuestasCrud;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\Rbac\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RolController extends Controller
{
    use RespuestasCrud;

    public function index(PermissionService $permissions)
    {
        $roles = $permissions->rolesWithPermissions();
        $dbRoles = Schema::hasTable('roles') ? Role::orderBy('nombre')->get() : collect();

        return view('admin.roles.index', compact('roles', 'dbRoles'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'descripcion' => 'nullable|string|max:255',
        ]);

        try {
            $role->update($validated);
        } catch (Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'actualizar rol');
        }

        return $this->respuestaExito($request, "Rol «{$role->nombre}» actualizado.", 'admin.roles.index');
    }
}
