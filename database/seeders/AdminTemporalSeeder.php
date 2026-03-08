<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminTemporalSeeder extends Seeder
{
    /**
     * Crea un usuario administrador temporal para acceder al panel
     */
    public function run(): void
    {
        // Buscar si ya existe un admin temporal
        $admin = User::where('email', 'admin@temp.com')->first();
        
        if ($admin) {
            // Actualizar credenciales si ya existe
            $admin->update([
                'nombre' => 'Admin',
                'apellido_paterno' => 'Temporal',
                'apellido_materno' => 'Sistema',
                'contrasena' => Hash::make('admin123'),
                'rol' => 'admin',
            ]);
            $this->command->info('✓ Usuario administrador temporal actualizado');
        } else {
            // Crear nuevo admin temporal
            User::create([
                'nombre' => 'Admin',
                'apellido_paterno' => 'Temporal',
                'apellido_materno' => 'Sistema',
                'email' => 'admin@temp.com',
                'contrasena' => Hash::make('admin123'),
                'rol' => 'admin',
            ]);
            $this->command->info('✓ Usuario administrador temporal creado');
        }

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->info('  CREDENCIALES TEMPORALES DE ADMINISTRADOR');
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->info('  Email: admin@temp.com');
        $this->command->info('  Contraseña: admin123');
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('⚠️  IMPORTANTE: Estas son credenciales temporales.');
        $this->command->info('   Cámbialas desde el panel de administración.');
        $this->command->info('');
    }
}






