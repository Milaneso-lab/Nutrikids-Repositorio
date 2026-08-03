<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespuestasCrud;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Contacto;
use App\Models\Comentario;
use App\Models\Discusion;
use App\Models\Menu;
use Throwable;

class ContenidoController extends Controller
{
    use RespuestasCrud;

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
        $menus = Menu::with('paciente')
            ->latest()
            ->get();

        return view('admin.contenido.menus', compact('menus'));
    }

    public function destroyMenu($id)
    {
        try {
            $menu = Menu::findOrFail($id);
            $menu->delete();

            return response()->json([
                'success' => true,
                'message' => 'Menú eliminado exitosamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el menú. Inténtalo de nuevo.',
            ], 500);
        }
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
                'message' => 'No se pudo eliminar el contacto. Inténtalo de nuevo.',
            ], 500);
        }
    }

    public function responderContacto(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'respuesta' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return $this->respuestaValidacion($request, $validator);
        }

        try {
            $contacto = Contacto::findOrFail($id);
            $contacto->fill([
                'respuesta' => $request->string('respuesta')->toString(),
                'respondido_en' => now(),
                'respondido_por_id' => $request->user()?->id_usuario,
            ])->save();
        } catch (Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'responder mensaje de contacto');
        }

        return $this->respuestaExito(
            $request,
            'Respuesta guardada correctamente.',
            'admin.contenido.index',
            ['respondido_en' => $contacto->respondido_en?->toDateTimeString()],
        );
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
