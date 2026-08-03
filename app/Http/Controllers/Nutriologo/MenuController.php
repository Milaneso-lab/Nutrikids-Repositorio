<?php

namespace App\Http\Controllers\Nutriologo;

use App\Http\Controllers\Concerns\RespuestasCrud;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Throwable;

class MenuController extends Controller
{
    use RespuestasCrud;

    private const MENSAJES = [
        'nombre.required' => 'Ponle un nombre al plan alimenticio.',
        'paciente_id.required' => 'Selecciona el paciente al que pertenece el plan.',
        'paciente_id.exists' => 'El paciente seleccionado ya no existe.',
        'descripcion.required' => 'Describe el contenido del plan.',
    ];

    public function index(Request $request)
    {
        $query = Menu::with('paciente')->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->string('estado'));
        }

        if ($request->filled('paciente_id')) {
            $query->where('paciente_id', $request->integer('paciente_id'));
        }

        $menus = $query->get();
        $pacientes = Paciente::orderBy('nombre')->get(['id', 'nombre', 'apellidos']);

        return view('nutriologo.menus.index', compact('menus', 'pacientes'));
    }

    public function create(Request $request)
    {
        $pacientes = Paciente::orderBy('nombre')->orderBy('apellidos')->get();
        $selectedPacienteId = $request->integer('paciente_id');

        return view('nutriologo.menus.create', compact('pacientes', 'selectedPacienteId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'paciente_id' => 'required|exists:pacientes,id',
            'descripcion' => 'required|string',
            'estado' => 'nullable|in:activo,borrador,archivado',
        ], self::MENSAJES);

        $validated['estado'] ??= Menu::ESTADO_ACTIVO;

        try {
            $menu = Menu::create($validated);
        } catch (Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'guardar plan alimenticio');
        }

        return $this->respuestaExito(
            $request,
            $menu->nino_id
                ? 'Plan guardado. Ya es visible en la app del padre.'
                : 'Plan guardado correctamente.',
            'nutriologo.menus.edit',
            ['menu' => $menu->id],
            ['id' => $menu->id],
        );
    }

    public function edit(Menu $id)
    {
        $menu = $id->load(['paciente', 'original']);
        $pacientes = Paciente::orderBy('nombre')->orderBy('apellidos')->get();
        $historial = Menu::where('paciente_id', $menu->paciente_id)
            ->where('id', '!=', $menu->id)
            ->latest()
            ->take(10)
            ->get();

        return view('nutriologo.menus.edit', compact('menu', 'pacientes', 'historial'));
    }

    public function update(Request $request, Menu $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'paciente_id' => 'required|exists:pacientes,id',
            'descripcion' => 'required|string',
            'estado' => 'nullable|in:activo,borrador,archivado',
        ], self::MENSAJES);

        try {
            $id->update($validated);
        } catch (Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'actualizar plan alimenticio');
        }

        return $this->respuestaExito(
            $request,
            'Plan actualizado correctamente.',
            'nutriologo.menus.edit',
            ['menu' => $id->id],
            ['id' => $id->id],
        );
    }

    public function duplicate(Request $request, Menu $id)
    {
        try {
            $copia = Menu::create([
                'nombre' => $id->nombre.' (copia)',
                'paciente_id' => $id->paciente_id,
                'descripcion' => $id->descripcion,
                'estado' => Menu::ESTADO_BORRADOR,
                'duplicado_de_id' => $id->id,
            ]);
        } catch (Throwable $e) {
            return $this->respuestaExcepcion($request, $e, 'duplicar plan alimenticio');
        }

        return $this->respuestaExito(
            $request,
            'Plan duplicado como borrador. Puedes editarlo antes de activarlo.',
            'nutriologo.menus.edit',
            ['menu' => $copia->id],
            ['id' => $copia->id],
        );
    }
}
