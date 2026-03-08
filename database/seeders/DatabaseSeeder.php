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
            AdminTemporalSeeder::class,   // admin@temp.com / admin123
            CredencialesSeeder::class,    // admin, nutriologo y padre con roles
            UsuarioSeeder::class,
            ComentarioSeeder::class,
            DiscusionSeeder::class,
        ]);
    }
}
