<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discusion extends Model
{
    protected $table = 'discusions';
    protected $primaryKey = 'id_discusion';
    
    public $timestamps = false; // Desactivar timestamps automáticos
    
    protected $fillable = [
        'tema',
        'descripcion',
        'id_usuario'
    ];
    
    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_usuario', 'id_usuario');
    }
    
    protected $casts = [
        'fecha_creacion' => 'datetime',
    ];
}
