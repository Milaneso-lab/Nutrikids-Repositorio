<?php

namespace App\Http\Controllers\Nutriologo;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion;
use App\Models\Paciente;
use Illuminate\Http\Request;

class RecomendacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Evaluacion::with('paciente')
            ->whereNotNull('recomendaciones')
            ->where('recomendaciones', '!=', '');

        if ($request->filled('paciente_id')) {
            $query->where('paciente_id', $request->integer('paciente_id'));
        }

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(function ($builder) use ($q) {
                $builder->where('recomendaciones', 'like', $q)
                    ->orWhereHas('paciente', function ($p) use ($q) {
                        $p->where('nombre', 'like', $q)->orWhere('apellidos', 'like', $q);
                    });
            });
        }

        $recomendaciones = $query->latest()->paginate(15)->withQueryString();
        $pacientes = Paciente::orderBy('nombre')->get(['id', 'nombre', 'apellidos']);

        return view('nutriologo.recomendaciones.index', compact('recomendaciones', 'pacientes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'recomendaciones' => 'required|string|min:10',
            'peso' => 'nullable|numeric|min:0',
            'talla' => 'nullable|numeric|min:0',
        ]);

        Evaluacion::create([
            'paciente_id' => $validated['paciente_id'],
            'peso' => $validated['peso'] ?? 0,
            'talla' => $validated['talla'] ?? 0,
            'recomendaciones' => $validated['recomendaciones'],
        ]);

        return back()->with('success', 'Recomendación registrada. Será visible para la familia en el portal del padre.');
    }
}
