<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            [
                'nombre' => 'María',
                'apellido_paterno' => 'González',
                'apellido_materno' => 'López',
                'email' => 'maria.gonzalez@example.com',
                'password' => Hash::make('Password123'),
            ],
            [
                'nombre' => 'Juan',
                'apellido_paterno' => 'Pérez',
                'apellido_materno' => 'Martínez',
                'email' => 'juan.perez@example.com',
                'password' => Hash::make('Password123'),
            ],
            [
                'nombre' => 'Ana',
                'apellido_paterno' => 'Rodríguez',
                'apellido_materno' => 'Sánchez',
                'email' => 'ana.rodriguez@example.com',
                'password' => Hash::make('Password123'),
            ],
        ];

        foreach ($usuarios as $usuario) {
            User::firstOrCreate(
                ['email' => $usuario['email']],
                $usuario
            );
        }
    }
}
