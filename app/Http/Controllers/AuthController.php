<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
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
                'errors' => $validator->errors()->all()
            ], 400);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->contrasena, $user->contrasena)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email o contraseña incorrectos.'
                ], 401);
            }

            // Iniciar sesión
            Auth::login($user);

            // Redirigir según el rol del usuario en la base de datos
            $redirectRoute = 'index';
            $rol = $user->rol ?? 'padre';
            
            if ($rol === 'admin') {
                $redirectRoute = 'admin.dashboard';
            } elseif ($rol === 'nutriologo') {
                $redirectRoute = 'nutriologo.dashboard';
            }

            $redirectUrl = route($redirectRoute);

            return response()->json([
                'success' => true,
                'message' => '¡Inicio de sesión exitoso!',
                'redirect' => $redirectUrl,
                'rol' => $rol
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en login: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor. Por favor, inténtalo más tarde.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
