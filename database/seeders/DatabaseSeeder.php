<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminTemporalSeeder::class, // Crear admin temporal primero
            UsuarioSeeder::class,
            ComentarioSeeder::class,
            DiscusionSeeder::class,
        ]);
    }
}
