<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    public const ESTADO_ACTIVO = 'activo';

    public const ESTADO_BORRADOR = 'borrador';

    public const ESTADO_ARCHIVADO = 'archivado';

    protected $table = 'menus';

    protected $fillable = [
        'nombre',
        'paciente_id',
        'nino_id',
        'descripcion',
        'estado',
        'duplicado_de_id',
    ];

    /**
     * La app móvil lista los planes por `nino_id`; el panel sólo conoce el
     * expediente. Copiamos el enlace para que el plan sea visible en ambos.
     */
    protected static function booted(): void
    {
        static::saving(function (self $menu): void {
            // Se recalcula también al reasignar el plan a otro expediente.
            if ($menu->paciente_id !== null && ($menu->nino_id === null || $menu->isDirty('paciente_id'))) {
                $menu->nino_id = Paciente::whereKey($menu->paciente_id)->value('nino_id');
            }
        });
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function nino()
    {
        return $this->belongsTo(Nino::class, 'nino_id');
    }

    public function original()
    {
        return $this->belongsTo(self::class, 'duplicado_de_id');
    }
}
