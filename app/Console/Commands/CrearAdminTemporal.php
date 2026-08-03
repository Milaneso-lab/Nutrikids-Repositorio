<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CrearAdminTemporal extends Command
{
    protected $signature = 'admin:crear-temporal
                            {--email= : Email del administrador (o ADMIN_TEMPORAL_EMAIL)}';

    protected $description = 'Crea o restablece un administrador de emergencia. La contraseña se toma de ADMIN_TEMPORAL_PASSWORD.';

    public function handle()
    {
        if (app()->environment('production')) {
            $this->error('Comando bloqueado en producción. Gestiona los administradores desde el panel.');

            return Command::FAILURE;
        }

        $email = $this->option('email') ?: env('ADMIN_TEMPORAL_EMAIL');
        $password = env('ADMIN_TEMPORAL_PASSWORD');

        if (!$email || !$password) {
            $this->error('Define ADMIN_TEMPORAL_EMAIL y ADMIN_TEMPORAL_PASSWORD en el archivo .env.');
            $this->line('Ejemplo:');
            $this->line('  ADMIN_TEMPORAL_EMAIL=admin.emergencia@nutrikids.com');
            $this->line('  ADMIN_TEMPORAL_PASSWORD=<contraseña fuerte>');

            return Command::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('ADMIN_TEMPORAL_PASSWORD debe tener al menos 8 caracteres.');

            return Command::FAILURE;
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
            $this->info("Administrador temporal actualizado: {$email}");
        } else {
            User::create($datos + ['email' => $email]);
            $this->info("Administrador temporal creado: {$email}");
        }

        $this->warn('La contraseña es la definida en ADMIN_TEMPORAL_PASSWORD; no se imprime por seguridad.');
        $this->warn('Cámbiala desde el panel de administración y retira la variable del .env.');

        return Command::SUCCESS;
    }
}
