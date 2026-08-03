<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespuestasCrud;
use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

class NutriologoController extends Controller
{
    use RespuestasCrud;

    public function index()
    {
        $nutriologos = User::where('rol', 'nutriologo')
            ->orderBy('nombre')
            ->get()
            ->map(function (User $n) {
                $citasAsignadas = Cita::where('id_nutriologo', $n->id_usuario)
                    ->where('estado', '!=', Cita::ESTADO_CANCELADA)
                    ->count();

                return [
                    'usuario' => $n,
                    'citas_activas' => $citasAsignadas,
                    'estado' => 'activo',
                ];
            });

        return view('admin.nutriologos.index', compact('nutriologos'));
    }

    public function create()
    {
        return view('admin.nutriologos.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|min:2|max:50',
            'apellido_paterno' => 'required|string|min:2|max:50',
            'apellido_materno' => 'nullable|string|max:50',
            'email' => 'required|email|max:100|unique:usuarios,email',
            'contrasena' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/'],
        ]);

        if ($validator->fails()) {
            return $this->respuestaValidacion($request, $validator);
        }

        try {
            // El modelo User deriva `rol_id` a partir de `rol` al guardar.
            User::create([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'contrasena' => Hash::make($request->contrasena),
                'rol' => 'nutriologo',
            ]);
        } catch (Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'registrar nutriólogo');
        }

        return $this->respuestaExito(
            $request,
            'Nutriólogo registrado correctamente.',
            'admin.nutriologos.index'
        );
    }

    public function edit(int $id)
    {
        $nutriologo = User::where('rol', 'nutriologo')->findOrFail($id);

        return view('admin.nutriologos.edit', compact('nutriologo'));
    }

    public function update(Request $request, int $id)
    {
        $nutriologo = User::where('rol', 'nutriologo')->findOrFail($id);

        $rules = [
            'nombre' => 'required|string|min:2|max:50',
            'apellido_paterno' => 'required|string|min:2|max:50',
            'apellido_materno' => 'nullable|string|max:50',
            'email' => 'required|email|max:100|unique:usuarios,email,'.$nutriologo->id_usuario.',id_usuario',
        ];

        if ($request->filled('contrasena')) {
            $rules['contrasena'] = ['string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/'];
        }

        $validated = $request->validate($rules);

        $nutriologo->nombre = $validated['nombre'];
        $nutriologo->apellido_paterno = $validated['apellido_paterno'];
        $nutriologo->apellido_materno = $validated['apellido_materno'] ?? null;
        $nutriologo->email = $validated['email'];

        if ($request->filled('contrasena')) {
            $nutriologo->contrasena = Hash::make($request->contrasena);
        }

        try {
            $nutriologo->save();
        } catch (Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'actualizar nutriólogo');
        }

        return $this->respuestaExito($request, 'Nutriólogo actualizado.', 'admin.nutriologos.index');
    }
}
