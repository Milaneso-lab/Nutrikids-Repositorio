<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    protected $table = 'instituciones';

    protected $fillable = [
        'nombre',
        'tipo',
        'ciudad',
        'contacto_email',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public const TIPOS = ['escuela', 'clinica', 'ong', 'gobierno', 'otro'];
}
