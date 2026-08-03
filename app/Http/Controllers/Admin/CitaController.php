<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\User;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function index()
    {
        $citas = Cita::with(['padre', 'nutriologo'])->orderByDesc('created_at')->get();
        $nutriologos = User::where('rol', 'nutriologo')->orderBy('nombre')->orderBy('apellido_paterno')->get();

        return view('admin.citas.index', compact('citas', 'nutriologos'));
    }

    public function asignar(Request $request, Cita $cita)
    {
        $request->validate([
            'id_nutriologo' => 'required|integer|exists:usuarios,id_usuario',
        ]);

        $nutri = User::where('id_usuario', $request->id_nutriologo)->where('rol', 'nutriologo')->first();
        if (! $nutri) {
            return back()->with('error', 'El usuario seleccionado no es nutriólogo.');
        }

        $cita->update([
            'id_nutriologo' => $request->id_nutriologo,
            'estado' => Cita::ESTADO_ASIGNADA,
        ]);

        return back()->with('success', 'Cita asignada al nutriólogo correctamente.');
    }

    public function estado(Request $request, Cita $cita)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,asignada,confirmada,cancelada',
        ]);

        $cita->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado de la cita actualizado.');
    }
}
