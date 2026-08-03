<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespuestasCrud;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    use RespuestasCrud;

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
            'email' => 'required|email|max:100|unique:usuarios,email',
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
            return $this->respuestaValidacion($request, $validator);
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

            return $this->respuestaExito(
                $request,
                "Usuario {$user->nombre} creado correctamente.",
                'admin.usuarios.index',
                ['usuario' => $user]
            );
        } catch (\Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'crear usuario');
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
            'email' => 'required|email|max:100|unique:usuarios,email,' . $id . ',id_usuario',
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
            return $this->respuestaValidacion($request, $validator);
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

            return $this->respuestaExito(
                $request,
                "Usuario {$usuario->nombre} actualizado correctamente.",
                'admin.usuarios.index',
                ['usuario' => $usuario]
            );
        } catch (\Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'actualizar usuario');
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $usuario = User::findOrFail($id);

            if ($usuario->id_usuario == Auth::id()) {
                $mensaje = 'No puedes eliminar tu propio usuario.';

                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $mensaje], 422);
                }

                return back()->with('error', $mensaje);
            }

            $nombre = $usuario->nombre;
            $usuario->delete();

            return $this->respuestaExito(
                $request,
                "Usuario {$nombre} eliminado correctamente.",
                'admin.usuarios.index'
            );
        } catch (\Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'eliminar usuario', 'admin.usuarios.index');
        }
    }
}
