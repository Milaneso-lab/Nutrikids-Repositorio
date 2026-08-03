<?php

namespace App\Services\Rbac;

use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class PermissionService
{
    public function rolesWithPermissions(): array
    {
        if (Schema::hasTable('roles') && Schema::hasTable('permisos')) {
            return Role::with('permisos')->orderBy('nombre')->get()->map(function (Role $role) {
                return [
                    'nombre' => $role->nombre,
                    'descripcion' => $role->descripcion,
                    'permisos' => $role->permisos->pluck('clave')->all(),
                ];
            })->all();
        }

        $matrix = [];
        foreach (config('nutrikids.roles', []) as $nombre => $descripcion) {
            $matrix[] = [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'permisos' => config("nutrikids.role_permissions.{$nombre}", []),
            ];
        }

        return $matrix;
    }

    public function allPermissions(): array
    {
        if (Schema::hasTable('permisos')) {
            return Permiso::orderBy('clave')->get()->map(fn (Permiso $p) => [
                'clave' => $p->clave,
                'descripcion' => $p->descripcion,
            ])->all();
        }

        return collect(config('nutrikids.permissions', []))
            ->map(fn ($desc, $clave) => ['clave' => $clave, 'descripcion' => $desc])
            ->values()
            ->all();
    }

    public function userCan(User $user, string $permission): bool
    {
        $rol = $user->rol ?? '';

        if (Schema::hasTable('roles') && Schema::hasTable('rol_permiso')) {
            $role = Role::where('nombre', $rol)->with('permisos')->first();
            if ($role) {
                return $role->permisos->contains('clave', $permission);
            }
        }

        return in_array($permission, config("nutrikids.role_permissions.{$rol}", []), true);
    }
}
