<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'Usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false; // La tabla Usuarios no tiene created_at ni updated_at
    
    // Mapeo de campos: Laravel usa 'password' pero la BD usa 'contrasena'
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'contrasena', // Nombre real en la base de datos
        'rol', // admin, nutriologo, padre
    ];
    
    // Para compatibilidad con Laravel Auth
    public function getAuthIdentifierName()
    {
        return 'id_usuario';
    }
    
    public function getAuthIdentifier()
    {
        return $this->id_usuario;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'contrasena', // Nombre real en la base de datos
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'contrasena' => 'hashed', // Nombre real en la base de datos
        ];
    }
    
    // Método para compatibilidad con Laravel Auth (espera 'password')
    public function getAuthPassword()
    {
        return $this->contrasena;
    }
}
