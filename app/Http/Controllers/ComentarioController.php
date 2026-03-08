<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ComentarioController extends Controller
{
    public function index()
    {
        try {
            // Si el usuario está logueado, mostrar solo sus comentarios
            if (Auth::check() && Auth::user()->rol === 'padre') {
                $comentarios = Comentario::where('id_usuario', Auth::id())
                    ->orderBy('fecha_comentario', 'desc')
                    ->get(['nombre', 'apellido', 'comentario', 'fecha_comentario', 'id_usuario']);
            } else {
                // Si no está logueado, no mostrar comentarios
                $comentarios = collect([]);
            }

            return response()->json([
                'success' => true,
                'comentarios' => $comentarios
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener comentarios.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Verificar que el usuario esté autenticado y sea padre
        if (!Auth::check() || Auth::user()->rol !== 'padre') {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión como padre para publicar comentarios.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'comentario' => 'required|string|min:5|max:1000',
        ], [
            'comentario.min' => 'El comentario debe tener al menos 5 caracteres',
            'comentario.max' => 'El comentario no puede exceder 1000 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()->all()
            ], 400);
        }

        try {
            $usuario = Auth::user();
            $comentario = Comentario::create([
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido_paterno,
                'comentario' => $request->comentario,
                'id_usuario' => $usuario->id_usuario,
                'fecha_comentario' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => '¡Comentario publicado correctamente!',
                'comentario' => $comentario
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el comentario. Por favor, inténtalo de nuevo.'
            ], 500);
        }
    }
}
