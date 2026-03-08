<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CrearAdminTemporal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:crear-temporal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea un usuario administrador temporal para acceder al panel de administración';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = 'admin@temp.com';
        $password = 'admin123';
        
        // Buscar si ya existe
        $admin = User::where('email', $email)->first();
        
        if ($admin) {
            // Actualizar credenciales
            $admin->update([
                'nombre' => 'Admin',
                'apellido_paterno' => 'Temporal',
                'apellido_materno' => 'Sistema',
                'contrasena' => Hash::make($password),
                'rol' => 'admin',
            ]);
            $this->info('✓ Usuario administrador temporal actualizado');
        } else {
            // Crear nuevo
            User::create([
                'nombre' => 'Admin',
                'apellido_paterno' => 'Temporal',
                'apellido_materno' => 'Sistema',
                'email' => $email,
                'contrasena' => Hash::make($password),
                'rol' => 'admin',
            ]);
            $this->info('✓ Usuario administrador temporal creado');
        }

        $this->newLine();
        $this->line('═══════════════════════════════════════════════════');
        $this->line('  CREDENCIALES TEMPORALES DE ADMINISTRADOR');
        $this->line('═══════════════════════════════════════════════════');
        $this->line('  Email: ' . $email);
        $this->line('  Contraseña: ' . $password);
        $this->line('═══════════════════════════════════════════════════');
        $this->newLine();
        $this->warn('⚠️  IMPORTANTE: Estas son credenciales temporales.');
        $this->warn('   Cámbialas desde el panel de administración.');
        $this->newLine();
        
        return Command::SUCCESS;
    }
}
