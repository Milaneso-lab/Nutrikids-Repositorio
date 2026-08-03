<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    protected $table = 'contactos';
    protected $primaryKey = 'id_contacto';
    
    public $timestamps = false; // Desactivar timestamps automáticos
    
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'mensaje',
        'respuesta',
        'respondido_en',
        'respondido_por_id',
    ];
    
    protected $casts = [
        'fecha_creacion' => 'datetime',
        'respondido_en' => 'datetime',
    ];

    public function respondidoPor()
    {
        return $this->belongsTo(User::class, 'respondido_por_id', 'id_usuario');
    }
}
