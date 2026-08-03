<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cita extends Model
{
    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_ASIGNADA = 'asignada';

    public const ESTADO_CONFIRMADA = 'confirmada';

    public const ESTADO_CANCELADA = 'cancelada';

    protected $table = 'citas';

    protected $fillable = [
        'id_padre',
        'id_nutriologo',
        'fecha_preferida',
        'franja',
        'telefono',
        'mensaje',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_preferida' => 'date',
        ];
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_padre', 'id_usuario');
    }

    public function nutriologo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_nutriologo', 'id_usuario');
    }
}
