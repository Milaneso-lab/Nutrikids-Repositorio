<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $table = 'Comentarios';
    protected $primaryKey = 'id_comentario';
    
    public $timestamps = false; // Desactivar timestamps automáticos
    
    protected $fillable = [
        'nombre',
        'apellido',
        'comentario',
        'id_usuario'
    ];
    
    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_usuario', 'id_usuario');
    }
    
    protected $casts = [
        'fecha_comentario' => 'datetime',
    ];
}
