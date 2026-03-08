<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CredencialesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Administrador
        $admin = User::where('email', 'admin@nutrikids.com')->first();
        if (!$admin) {
            User::create([
                'nombre' => 'Administrador',
                'apellido_paterno' => 'Sistema',
                'apellido_materno' => 'NutriKids',
                'email' => 'admin@nutrikids.com',
                'contrasena' => Hash::make('admin123'),
                'rol' => 'admin',
            ]);
        } else {
            $admin->update([
                'rol' => 'admin',
                'contrasena' => Hash::make('admin123'),
            ]);
        }

        // Nutriólogo
        $nutriologo = User::where('email', 'nutriologo@nutrikids.com')->first();
        if (!$nutriologo) {
            User::create([
                'nombre' => 'Sandra',
                'apellido_paterno' => 'Olmos',
                'apellido_materno' => 'García',
                'email' => 'nutriologo@nutrikids.com',
                'contrasena' => Hash::make('nutriologo123'),
                'rol' => 'nutriologo',
            ]);
        } else {
            $nutriologo->update([
                'rol' => 'nutriologo',
                'contrasena' => Hash::make('nutriologo123'),
            ]);
        }

        // Padre (usuario normal)
        $padre = User::where('email', 'padre@nutrikids.com')->first();
        if (!$padre) {
            User::create([
                'nombre' => 'Carlos',
                'apellido_paterno' => 'Ramírez',
                'apellido_materno' => 'López',
                'email' => 'padre@nutrikids.com',
                'contrasena' => Hash::make('padre123'),
                'rol' => 'padre',
            ]);
        } else {
            $padre->update([
                'rol' => 'padre',
                'contrasena' => Hash::make('padre123'),
            ]);
        }

        $this->command->info('✓ Credenciales creadas exitosamente:');
        $this->command->info('');
        $this->command->info('ADMINISTRADOR:');
        $this->command->info('  Email: admin@nutrikids.com');
        $this->command->info('  Contraseña: admin123');
        $this->command->info('');
        $this->command->info('NUTRIÓLOGO:');
        $this->command->info('  Email: nutriologo@nutrikids.com');
        $this->command->info('  Contraseña: nutriologo123');
        $this->command->info('');
        $this->command->info('PADRE:');
        $this->command->info('  Email: padre@nutrikids.com');
        $this->command->info('  Contraseña: padre123');
    }
}
