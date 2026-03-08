<?php

namespace App\Http\Controllers;

use App\Models\Discusion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class DiscusionController extends Controller
{
    public function index()
    {
        try {
            $discusiones = Discusion::with('usuario')
                ->orderBy('fecha_creacion', 'desc')
                ->get(['id_discusion', 'tema', 'descripcion', 'fecha_creacion', 'id_usuario']);

            return response()->json([
                'success' => true,
                'discusiones' => $discusiones,
                'usuario_actual' => Auth::check() ? Auth::id() : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener discusiones.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Verificar que el usuario esté autenticado y sea padre
        if (!Auth::check() || Auth::user()->rol !== 'padre') {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión como padre para crear discusiones.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'tema' => 'required|string|min:5|max:255',
            'descripcion' => 'required|string|min:10',
        ], [
            'tema.min' => 'El tema debe tener al menos 5 caracteres',
            'tema.max' => 'El tema no puede exceder 255 caracteres',
            'descripcion.min' => 'La descripción debe tener al menos 10 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()->all()
            ], 400);
        }

        try {
            $discusion = Discusion::create([
                'tema' => $request->tema,
                'descripcion' => $request->descripcion,
                'id_usuario' => Auth::id(),
                'fecha_creacion' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => '¡Discusión creada exitosamente!',
                'discusion' => $discusion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la discusión. Por favor, inténtalo de nuevo.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Verificar que el usuario esté autenticado y sea padre
        if (!Auth::check() || Auth::user()->rol !== 'padre') {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión como padre para editar discusiones.'
            ], 401);
        }

        $discusion = Discusion::findOrFail($id);

        // Verificar que el usuario sea el dueño de la discusión
        if ($discusion->id_usuario != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar esta discusión.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'tema' => 'required|string|min:5|max:255',
            'descripcion' => 'required|string|min:10',
        ], [
            'tema.min' => 'El tema debe tener al menos 5 caracteres',
            'tema.max' => 'El tema no puede exceder 255 caracteres',
            'descripcion.min' => 'La descripción debe tener al menos 10 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()->all()
            ], 400);
        }

        try {
            $discusion->update($request->only(['tema', 'descripcion']));

            return response()->json([
                'success' => true,
                'message' => '¡Discusión actualizada exitosamente!',
                'discusion' => $discusion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la discusión. Por favor, inténtalo de nuevo.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        // Verificar que el usuario esté autenticado y sea padre
        if (!Auth::check() || Auth::user()->rol !== 'padre') {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión como padre para eliminar discusiones.'
            ], 401);
        }

        $discusion = Discusion::findOrFail($id);

        // Verificar que el usuario sea el dueño de la discusión
        if ($discusion->id_usuario != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar esta discusión.'
            ], 403);
        }

        try {
            $discusion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Discusión eliminada exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la discusión. Por favor, inténtalo de nuevo.'
            ], 500);
        }
    }
}
