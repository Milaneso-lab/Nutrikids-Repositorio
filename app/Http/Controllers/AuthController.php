<?php

namespace App\Http\Controllers;

use App\Services\Auth\LoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(private LoginService $loginService)
    {
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'contrasena' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()->all(),
            ], 400);
        }

        try {
            $user = $this->loginService->attempt(
                $request->string('email'),
                $request->string('contrasena')
            );

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email o contraseña incorrectos.',
                ], 401);
            }

            $rol = $user->rol ?? 'padre';

            if (Schema::hasColumn('usuarios', 'estado')) {
                $estado = $user->estado ?? 'activo';
                if ($estado === 'suspendido') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tu cuenta está suspendida. Contacta al administrador.',
                    ], 403);
                }
                if ($estado === 'pendiente_verificacion' && $rol === 'padre') {
                    $user->estado = 'activo';
                    $user->save();
                } elseif ($estado !== 'activo') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tu cuenta no está activa. Contacta al administrador.',
                    ], 403);
                }
            }

            $this->loginService->loginUser($user);

            return response()->json([
                'success' => true,
                'message' => '¡Inicio de sesión exitoso!',
                'redirect' => $this->loginService->redirectUrlFor($user, $request),
                'rol' => $rol,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en login: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'No se pudo iniciar sesión. Inténtalo de nuevo.',
            ], 500);
        }
    }
}
