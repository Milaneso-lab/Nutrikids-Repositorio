<?php

namespace App\Http\Controllers\Nutriologo;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluacionController extends Controller
{
    public function index()
    {
        $evaluaciones = Evaluacion::with('paciente')
            ->latest()
            ->get();

        return view('nutriologo.evaluaciones.index', compact('evaluaciones'));
    }

    public function create(Request $request)
    {
        $pacientes = Paciente::orderBy('nombre')->orderBy('apellidos')->get();
        $selectedPacienteId = $request->integer('paciente_id');

        return view('nutriologo.evaluaciones.create', compact('pacientes', 'selectedPacienteId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'peso' => 'required|numeric|min:0',
            'talla' => 'required|numeric|min:0',
            'recomendaciones' => 'nullable|string',
        ]);

        $evaluacion = Evaluacion::create([
            'paciente_id' => $validated['paciente_id'],
            'nutriologo_id' => Auth::id(),
            'peso' => $validated['peso'],
            'talla' => $validated['talla'],
            'recomendaciones' => $validated['recomendaciones'] ?? null,
        ]);

        return redirect()
            ->route('nutriologo.evaluaciones.edit', $evaluacion)
            ->with('success', 'Evaluación guardada correctamente (IMC '.($evaluacion->imc ?? 'no calculado').').');
    }

    public function edit(Evaluacion $id)
    {
        $evaluacion = $id->load('paciente');

        return view('nutriologo.evaluaciones.edit', compact('evaluacion'));
    }

    public function update(Request $request, Evaluacion $id)
    {
        $validated = $request->validate([
            'peso' => 'required|numeric|min:0',
            'talla' => 'required|numeric|min:0',
            'recomendaciones' => 'nullable|string',
        ]);

        $id->update($validated);

        return redirect()
            ->route('nutriologo.evaluaciones.edit', $id)
            ->with('success', 'Evaluación actualizada correctamente.');
    }
}
