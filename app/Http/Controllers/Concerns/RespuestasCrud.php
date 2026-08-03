<?php

namespace App\Http\Controllers\Concerns;

use App\Support\MensajesUsuario;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Unifica la respuesta de las operaciones CRUD del panel.
 *
 * Los formularios Blade se envían como POST normal y esperan una redirección con
 * mensaje flash; el JavaScript del panel usa fetch y espera JSON. Antes cada
 * controlador elegía uno de los dos, así que la mitad de las pantallas mostraba
 * un volcado JSON en lugar de confirmar la operación.
 */
trait RespuestasCrud
{
    protected function respuestaExito(
        Request $request,
        string $mensaje,
        string $rutaRedireccion,
        array $datos = [],
        array $parametrosRuta = []
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $mensaje] + $datos);
        }

        return redirect()->route($rutaRedireccion, $parametrosRuta)->with('success', $mensaje);
    }

    protected function respuestaValidacion(
        Request $request,
        ValidatorContract $validator
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Revisa los datos del formulario.',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        return back()->withErrors($validator)->withInput();
    }

    /**
     * Traduce la excepción a un mensaje accionable y deja el detalle técnico en el log.
     */
    protected function respuestaExcepcion(
        Request $request,
        Throwable $e,
        string $contexto,
        ?string $rutaRedireccion = null
    ): JsonResponse|RedirectResponse {
        Log::error("[CRUD] {$contexto}", [
            'excepcion' => $e::class,
            'mensaje' => $e->getMessage(),
            'usuario_id' => $request->user()?->getAuthIdentifier(),
            'ruta' => $request->fullUrl(),
        ]);

        $mensaje = $this->mensajeAmigable($e, $contexto);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $mensaje,
            ], 500);
        }

        $redireccion = $rutaRedireccion ? redirect()->route($rutaRedireccion) : back()->withInput();

        return $redireccion->with('error', $mensaje);
    }

    private function mensajeAmigable(Throwable $e, string $contexto): string
    {
        $texto = $e->getMessage();
        if (MensajesUsuario::esMensajeUsuario($texto)) {
            return trim($texto);
        }

        $sqlState = $e instanceof \PDOException ? ($e->getCode() ?: '') : '';

        return match (true) {
            $sqlState === '23505' || str_contains($texto, 'unique constraint') || str_contains($texto, 'Unique violation')
                => 'Ya existe un registro con esos datos. Revisa el correo o el identificador.',
            $sqlState === '23503' || str_contains($texto, 'foreign key constraint')
                => 'No se puede completar porque el registro está vinculado a otra información.',
            $sqlState === '23502' || str_contains($texto, 'not-null constraint')
                => 'Faltan datos obligatorios para guardar el registro.',
            default => MensajesUsuario::GENERICO,
        };
    }
}
