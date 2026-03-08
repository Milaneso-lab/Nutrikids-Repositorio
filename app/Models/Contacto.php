<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    protected $table = 'Contactos';
    protected $primaryKey = 'id_contacto';
    
    public $timestamps = false; // Desactivar timestamps automáticos
    
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'mensaje'
    ];
    
    protected $casts = [
        'fecha_creacion' => 'datetime',
    ];
}
