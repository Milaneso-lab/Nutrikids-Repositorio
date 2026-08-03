<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Throwable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROL_POR_DEFECTO = 'padre';

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    /** Cache por petición del mapa nombre de rol => id, para no consultar en cada save(). */
    private static ?array $mapaRoles = null;

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'contrasena',
        'rol',
        'rol_id',
        'telefono',
        'especialidad',
        'disponibilidad',
        'foto_path',
        'estado',
    ];

    protected $hidden = [
        'contrasena',
    ];

    /**
     * `usuarios` mantiene el rol duplicado: `rol` (texto, usado por la UI y el
     * middleware) y `rol_id` (clave foránea NOT NULL hacia `roles`, usada por RBAC).
     * Sincronizarlos aquí garantiza que cualquier ruta de escritura —panel admin,
     * registro público, seeders, tinker— cumpla la restricción sin duplicar lógica.
     */
    protected static function booted(): void
    {
        static::saving(function (self $usuario): void {
            $usuario->alinearRolYRolId();
        });
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    protected function alinearRolYRolId(): void
    {
        $mapa = self::mapaRoles();

        if ($mapa === []) {
            return;
        }

        $rol = is_string($this->rol) ? $this->rol : null;

        if ($rol !== null && isset($mapa[$rol])) {
            if ((int) $this->rol_id !== $mapa[$rol]) {
                $this->rol_id = $mapa[$rol];
            }

            return;
        }

        if ($this->rol_id !== null) {
            $nombre = array_search((int) $this->rol_id, $mapa, true);
            if ($nombre !== false) {
                $this->rol = $nombre;

                return;
            }
        }

        if (isset($mapa[self::ROL_POR_DEFECTO])) {
            $this->rol = self::ROL_POR_DEFECTO;
            $this->rol_id = $mapa[self::ROL_POR_DEFECTO];
        }
    }

    /** @return array<string, int> */
    private static function mapaRoles(): array
    {
        if (self::$mapaRoles !== null) {
            return self::$mapaRoles;
        }

        try {
            self::$mapaRoles = Schema::hasTable('roles')
                ? Role::pluck('id', 'nombre')->map(fn ($id) => (int) $id)->all()
                : [];
        } catch (Throwable) {
            // Durante `migrate:fresh` la tabla puede no existir todavía.
            self::$mapaRoles = [];
        }

        return self::$mapaRoles;
    }

    public static function olvidarMapaRoles(): void
    {
        self::$mapaRoles = null;
    }

    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }
}
