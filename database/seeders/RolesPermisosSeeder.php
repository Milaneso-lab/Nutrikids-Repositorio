<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RolesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permisos')) {
            $this->command?->warn('Tablas RBAC no disponibles; se usará config/nutrikids.php');

            return;
        }

        $permisosConfig = config('nutrikids.permissions', []);
        $permisoIds = [];

        foreach ($permisosConfig as $clave => $descripcion) {
            $permiso = Permiso::firstOrCreate(
                ['clave' => $clave],
                ['descripcion' => $descripcion]
            );
            $permisoIds[$clave] = $permiso->id;
        }

        foreach (config('nutrikids.roles', []) as $nombre => $descripcion) {
            $role = Role::firstOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion]
            );

            $claves = config("nutrikids.role_permissions.{$nombre}", []);
            $ids = collect($claves)->map(fn ($c) => $permisoIds[$c] ?? null)->filter()->values()->all();
            $role->permisos()->sync($ids);
        }

        $this->command?->info('✓ Roles y permisos sincronizados.');
    }
}
