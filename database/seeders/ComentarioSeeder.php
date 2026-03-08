<?php

namespace Database\Seeders;

use App\Models\Comentario;
use Illuminate\Database\Seeder;

class ComentarioSeeder extends Seeder
{
    public function run(): void
    {
        $comentarios = [
            [
                'nombre' => 'Laura',
                'apellido' => 'Fernández',
                'comentario' => 'Excelente información sobre nutrición infantil. Me ha ayudado mucho con mi hijo.',
            ],
            [
                'nombre' => 'Carlos',
                'apellido' => 'Mendoza',
                'comentario' => 'Muy útil la calculadora de IMC. Gracias por compartir esta herramienta.',
            ],
            [
                'nombre' => 'Patricia',
                'apellido' => 'Vargas',
                'comentario' => 'Los consejos sobre obesidad infantil son muy claros y fáciles de seguir.',
            ],
            [
                'nombre' => 'Roberto',
                'apellido' => 'Silva',
                'comentario' => 'La atención profesional es excelente. Recomiendo totalmente NutriKids.',
            ],
        ];

        foreach ($comentarios as $comentario) {
            Comentario::create($comentario);
        }
    }
}
