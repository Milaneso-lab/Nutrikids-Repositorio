<?php

namespace App\Support;

final class MensajesUsuario
{
    public const GENERICO = 'No se pudo completar la acción. Inténtalo de nuevo.';

    public const GUARDAR = 'No se pudo guardar. Revisa los datos e inténtalo de nuevo.';

    private const PATRON_TECNICO = '/HTTP|API|FastAPI|SQL|SQLSTATE|Connection|migraci|JSON|CSRF|token|Exception|Traceback|could not|foreign key|unique constraint|23505|23503|23502|<html|502|503|504/i';

    public static function esMensajeUsuario(?string $mensaje): bool
    {
        if ($mensaje === null) {
            return false;
        }
        $texto = trim($mensaje);
        if ($texto === '' || strlen($texto) > 500) {
            return false;
        }

        return ! preg_match(self::PATRON_TECNICO, $texto);
    }

    public static function sanitizar(?string $mensaje, string $fallback = self::GENERICO): string
    {
        return self::esMensajeUsuario($mensaje) ? trim($mensaje) : $fallback;
    }
}
