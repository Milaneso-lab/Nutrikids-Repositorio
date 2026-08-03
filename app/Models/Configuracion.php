<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Configuración del sistema como pares clave/valor en PostgreSQL.
 */
class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $primaryKey = 'clave';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['clave', 'valor'];

    /** Claves editables desde el panel de administración, con su valor inicial. */
    public const PREDETERMINADOS = [
        'nombre_sistema' => 'NutriKids',
        'email_contacto' => 'contacto@nutrikids.com',
        'telefono_contacto' => '',
        'politica_privacidad' => '',
        'terminos_condiciones' => '',
    ];

    public const CLAVES = [
        'nombre_sistema',
        'email_contacto',
        'telefono_contacto',
        'politica_privacidad',
        'terminos_condiciones',
    ];

    /** @return array<string, string|null> */
    public static function todas(): array
    {
        $guardadas = static::whereIn('clave', self::CLAVES)->pluck('valor', 'clave')->all();

        return array_replace(self::PREDETERMINADOS, array_filter($guardadas, fn ($v) => $v !== null));
    }

    /** @param array<string, string|null> $valores */
    public static function guardarVarias(array $valores): void
    {
        foreach ($valores as $clave => $valor) {
            if (! in_array($clave, self::CLAVES, true)) {
                continue;
            }

            static::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
        }
    }
}
