<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Administrador de emergencia para recuperar el acceso al panel.
 *
 * No forma parte de DatabaseSeeder: las credenciales normales las provee
 * CredencialesSeeder. Este seeder existe sólo como vía de recuperación y exige
 * ADMIN_TEMPORAL_EMAIL y ADMIN_TEMPORAL_PASSWORD en el entorno.
 *
 * Uso: php artisan db:seed --class=AdminTemporalSeeder
 */
class AdminTemporalSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('AdminTemporalSeeder está bloqueado en producción.');

            return;
        }

        $email = env('ADMIN_TEMPORAL_EMAIL');
        $password = env('ADMIN_TEMPORAL_PASSWORD');

        if (!$email || !$password) {
            $this->command->error('Define ADMIN_TEMPORAL_EMAIL y ADMIN_TEMPORAL_PASSWORD en el archivo .env.');

            return;
        }

        $datos = [
            'nombre' => 'Admin',
            'apellido_paterno' => 'Temporal',
            'apellido_materno' => 'Sistema',
            'contrasena' => Hash::make($password),
            'rol' => 'admin',
        ];

        $admin = User::where('email', $email)->first();

        if ($admin) {
            $admin->update($datos);
            $this->command->info("Administrador temporal actualizado: {$email}");
        } else {
            User::create($datos + ['email' => $email]);
            $this->command->info("Administrador temporal creado: {$email}");
        }

        $this->command->warn('La contraseña no se imprime: es la definida en ADMIN_TEMPORAL_PASSWORD.');
    }
}
