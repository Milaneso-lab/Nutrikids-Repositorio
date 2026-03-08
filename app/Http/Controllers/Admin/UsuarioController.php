<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('id_usuario', 'desc')->get();
        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('admin.usuarios.create');
    }

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
            'rol' => 'required|in:admin,nutriologo',
        ], [
            'nombre.regex' => 'El nombre debe contener solo letras y espacios',
            'apellido_paterno.regex' => 'El apellido paterno debe contener solo letras y espacios',
            'apellido_materno.regex' => 'El apellido materno debe contener solo letras y espacios',
            'contrasena.regex' => 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números',
            'email.unique' => 'El email ya está registrado.',
            'rol.required' => 'Debes seleccionar un rol para el usuario.',
            'rol.in' => 'El rol debe ser administrador o nutriólogo.',
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
                'rol' => $request->rol,
            ]);

            return response()->json([
                'success' => true,
                'message' => '¡Usuario creado exitosamente!',
                'usuario' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el usuario. Por favor, inténtalo de nuevo.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        return view('admin.usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|min:2|max:50|regex:/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/',
            'apellido_paterno' => 'required|string|min:2|max:50|regex:/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/',
            'apellido_materno' => 'nullable|string|min:2|max:50|regex:/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/',
            'email' => 'required|email|max:100|unique:Usuarios,email,' . $id . ',id_usuario',
            'contrasena' => 'nullable|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            'rol' => 'required|in:admin,nutriologo,padre',
        ], [
            'nombre.regex' => 'El nombre debe contener solo letras y espacios',
            'apellido_paterno.regex' => 'El apellido paterno debe contener solo letras y espacios',
            'apellido_materno.regex' => 'El apellido materno debe contener solo letras y espacios',
            'contrasena.regex' => 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números',
            'email.unique' => 'El email ya está registrado.',
            'rol.required' => 'Debes seleccionar un rol para el usuario.',
            'rol.in' => 'El rol debe ser administrador, nutriólogo o padre.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()->all()
            ], 400);
        }

        try {
            $data = [
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'rol' => $request->rol,
            ];

            if ($request->filled('contrasena')) {
                $data['contrasena'] = Hash::make($request->contrasena);
            }

            $usuario->update($data);

            return response()->json([
                'success' => true,
                'message' => '¡Usuario actualizado exitosamente!',
                'usuario' => $usuario
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el usuario. Por favor, inténtalo de nuevo.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $usuario = User::findOrFail($id);
            
            // No permitir eliminar al usuario actual
            if ($usuario->id_usuario == Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propio usuario.'
                ], 400);
            }

            $usuario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
