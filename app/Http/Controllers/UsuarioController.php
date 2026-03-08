<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|min:2|max:50|regex:/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/',
            'apellido_paterno' => 'required|string|min:2|max:50|regex:/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/',
            'apellido_materno' => 'nullable|string|min:2|max:50|regex:/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/',
            'email' => 'required|email|max:100|unique:Usuarios,email',
            'contrasena' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            ],
        ], [
            'nombre.regex' => 'El nombre debe contener solo letras y espacios',
            'apellido_paterno.regex' => 'El apellido paterno debe contener solo letras y espacios',
            'apellido_materno.regex' => 'El apellido materno debe contener solo letras y espacios',
            'contrasena.regex' => 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números',
            'email.unique' => 'El email ya está registrado. Por favor, usa otro email o inicia sesión.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()->all()
            ], 400);
        }

        try {
            $user = User::create([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'contrasena' => Hash::make($request->contrasena),
                'rol' => 'padre', // Los usuarios que se registran desde login tienen rol "padre"
            ]);

            // Loguear automáticamente al usuario después de registrarse
            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => '¡Usuario registrado correctamente! Has iniciado sesión automáticamente.',
                'usuario' => [
                    'id_usuario' => $user->id_usuario,
                    'nombre' => $user->nombre,
                    'apellido_paterno' => $user->apellido_paterno,
                    'apellido_materno' => $user->apellido_materno,
                    'email' => $user->email,
                    'rol' => $user->rol
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al registrar usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el usuario. Por favor, inténtalo de nuevo.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
