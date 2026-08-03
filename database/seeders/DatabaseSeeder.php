<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Sembrado por defecto: sólo datos de configuración (roles, permisos y las
 * credenciales de acceso por rol). Ninguna tabla clínica ni de comunidad recibe
 * contenido ficticio: se pueblan con información real desde los portales,
 * la API y la app móvil.
 *
 * El contenido de demostración vive en DemoContenidoSeeder y debe invocarse
 * de forma explícita en entornos locales.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CredencialesSeeder::class,
            RolesPermisosSeeder::class,
        ]);
    }
}
