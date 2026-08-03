<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespuestasCrud;
use App\Http\Controllers\Controller;
use App\Models\Institucion;
use Illuminate\Http\Request;

class InstitucionController extends Controller
{
    use RespuestasCrud;

    public function index()
    {
        $instituciones = Institucion::orderBy('nombre')->get();

        return view('admin.instituciones.index', compact('instituciones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'tipo' => 'required|in:'.implode(',', Institucion::TIPOS),
            'ciudad' => 'nullable|string|max:100',
            'contacto_email' => 'nullable|email|max:120',
        ]);

        try {
            $institucion = Institucion::create($validated + ['activa' => true]);

            return $this->respuestaExito(
                $request,
                "Institución {$institucion->nombre} registrada correctamente.",
                'admin.instituciones.index',
                ['institucion' => $institucion]
            );
        } catch (\Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'registrar institución');
        }
    }

    public function toggle(Request $request, string $id)
    {
        try {
            $institucion = Institucion::findOrFail($id);
            $institucion->activa = ! $institucion->activa;
            $institucion->save();

            $estado = $institucion->activa ? 'activada' : 'desactivada';

            return $this->respuestaExito(
                $request,
                "Institución {$institucion->nombre} {$estado}.",
                'admin.instituciones.index'
            );
        } catch (\Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'cambiar estado de institución', 'admin.instituciones.index');
        }
    }
}
