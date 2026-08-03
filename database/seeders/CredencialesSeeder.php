<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CredencialesSeeder extends Seeder
{
    /** Credenciales oficiales de prueba — Fase 6 */
    public const USERS = [
        [
            'nombre' => 'Administrador',
            'apellido_paterno' => 'Sistema',
            'apellido_materno' => 'NutriKids',
            'email' => 'admin@nutrikids.com',
            'contrasena' => 'Admin123*',
            'rol' => 'admin',
        ],
        [
            'nombre' => 'Sandra',
            'apellido_paterno' => 'Olmos',
            'apellido_materno' => 'García',
            'email' => 'nutriologo@nutrikids.com',
            'contrasena' => 'Nutri123*',
            'rol' => 'nutriologo',
        ],
        [
            'nombre' => 'Carlos',
            'apellido_paterno' => 'Ramírez',
            'apellido_materno' => 'López',
            'email' => 'padre@nutrikids.com',
            'contrasena' => 'Padre123*',
            'rol' => 'padre',
        ],
    ];

    public function run(): void
    {
        foreach (self::USERS as $data) {
            $user = User::firstOrNew(['email' => $data['email']]);
            $user->nombre = $data['nombre'];
            $user->apellido_paterno = $data['apellido_paterno'];
            $user->apellido_materno = $data['apellido_materno'];
            $user->rol = $data['rol'];
            $user->contrasena = Hash::make($data['contrasena']);

            if (Schema::hasColumn('usuarios', 'estado')) {
                $user->estado = 'activo';
            }

            if (Schema::hasColumn('usuarios', 'rol_id') && Schema::hasTable('roles')) {
                $role = Role::where('nombre', $data['rol'])->first();
                if ($role) {
                    $user->rol_id = $role->id;
                }
            }

            $user->save();
        }

        $this->command?->info('✓ Usuarios de prueba Fase 6 creados/actualizados:');
        $this->command?->info('  admin@nutrikids.com / Admin123* (Administrador)');
        $this->command?->info('  nutriologo@nutrikids.com / Nutri123* (Nutriólogo)');
        $this->command?->info('  padre@nutrikids.com / Padre123* (Padre)');
    }
}
