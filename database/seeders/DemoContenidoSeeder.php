<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Contenido de demostración para desarrollo local: usuarios padre de ejemplo,
 * comentarios y discusiones de la comunidad.
 *
 * NO forma parte del sembrado por defecto y no debe ejecutarse en entornos
 * compartidos ni productivos: introduce datos que no corresponden a personas
 * reales y usuarios con contraseñas débiles.
 *
 * Uso explícito:
 *   php artisan db:seed --class=DemoContenidoSeeder
 */
class DemoContenidoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('DemoContenidoSeeder está bloqueado en producción.');

            return;
        }

        $this->command->warn('Insertando contenido de demostración (no son datos reales).');

        $this->call([
            UsuarioSeeder::class,
            ComentarioSeeder::class,
            DiscusionSeeder::class,
        ]);
    }
}
