<?php

namespace Database\Seeders;

use App\Models\Discusion;
use Illuminate\Database\Seeder;

class DiscusionSeeder extends Seeder
{
    public function run(): void
    {
        $discusiones = [
            [
                'tema' => '¿Cómo prevenir la obesidad infantil?',
                'descripcion' => 'Compartamos consejos y estrategias para prevenir la obesidad en nuestros hijos desde temprana edad.',
            ],
            [
                'tema' => 'Recetas saludables para niños',
                'descripcion' => 'Hablemos sobre recetas nutritivas y deliciosas que los niños disfrutarán mientras se alimentan bien.',
            ],
            [
                'tema' => 'Actividad física en la infancia',
                'descripcion' => 'Discutamos la importancia de la actividad física y cómo motivar a los niños a mantenerse activos.',
            ],
            [
                'tema' => 'Alimentación balanceada en la escuela',
                'descripcion' => 'Compartamos ideas sobre cómo preparar loncheras saludables y nutritivas para nuestros hijos.',
            ],
        ];

        foreach ($discusiones as $discusion) {
            Discusion::create($discusion);
        }
    }
}
