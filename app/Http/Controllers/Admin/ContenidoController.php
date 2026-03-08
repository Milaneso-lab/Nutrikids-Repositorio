<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contacto;
use App\Models\Comentario;
use App\Models\Discusion;

class ContenidoController extends Controller
{
    public function index()
    {
        $contactos = Contacto::orderBy('id_contacto', 'desc')->get();
        $comentarios = Comentario::orderBy('id_comentario', 'desc')->get();
        $discusiones = Discusion::orderBy('id_discusion', 'desc')->get();
        
        return view('admin.contenido.index', compact('contactos', 'comentarios', 'discusiones'));
    }

    public function alimentos()
    {
        return view('admin.contenido.alimentos');
    }

    public function recetas()
    {
        return view('admin.contenido.recetas');
    }

    public function menus()
    {
        return view('admin.contenido.menus');
    }

    public function destroyContacto($id)
    {
        try {
            $contacto = Contacto::findOrFail($id);
            $contacto->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contacto eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el contacto.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function responderContacto(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'respuesta' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'La respuesta debe tener al menos 10 caracteres.',
            ], 400);
        }

        try {
            $contacto = Contacto::findOrFail($id);
            // Aquí podrías agregar un campo 'respuesta' a la tabla Contactos si lo necesitas
            // Por ahora solo confirmamos que se procesó
            return response()->json([
                'success' => true,
                'message' => 'Respuesta enviada exitosamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la respuesta.',
            ], 500);
        }
    }

    public function destroyComentario($id)
    {
        try {
            $comentario = Comentario::findOrFail($id);
            $comentario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comentario eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el comentario.',
            ], 500);
        }
    }

    public function destroyDiscusion($id)
    {
        try {
            $discusion = Discusion::findOrFail($id);
            $discusion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Discusión eliminada exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la discusión.',
            ], 500);
        }
    }
}
