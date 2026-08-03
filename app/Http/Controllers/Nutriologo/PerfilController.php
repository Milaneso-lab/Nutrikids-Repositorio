<?php

namespace App\Http\Controllers\Nutriologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    public function index()
    {
        return view('nutriologo.perfil.index', [
            'usuario' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'email' => 'required|email|max:100|unique:usuarios,email,'.$user->id_usuario.',id_usuario',
            'telefono' => 'nullable|string|max:30',
            'especialidad' => 'nullable|string|max:120',
            'disponibilidad' => 'nullable|string|max:255',
            'contrasena' => 'nullable|string|min:8',
        ]);

        $user->nombre = $validated['nombre'];
        $user->apellido_paterno = $validated['apellido_paterno'];
        $user->apellido_materno = $validated['apellido_materno'] ?? null;
        $user->email = $validated['email'];
        $user->telefono = $validated['telefono'] ?? null;
        $user->especialidad = $validated['especialidad'] ?? null;
        $user->disponibilidad = $validated['disponibilidad'] ?? null;

        if ($request->filled('contrasena')) {
            $user->contrasena = Hash::make($validated['contrasena']);
        }

        if ($request->hasFile('foto')) {
            $request->validate(['foto' => 'image|max:2048']);
            if ($user->foto_path) {
                Storage::disk('public')->delete($user->foto_path);
            }
            $user->foto_path = $request->file('foto')->store('nutriologos', 'public');
        }

        $user->save();

        return back()->with('success', 'Perfil profesional actualizado.');
    }
}
