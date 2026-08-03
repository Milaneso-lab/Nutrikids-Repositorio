<?php

namespace App\Http\Controllers\Nutriologo;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Menu;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitaController extends Controller
{
    public function index()
    {
        $uid = Auth::id();

        $pendientes = Cita::with('padre')
            ->where('estado', Cita::ESTADO_PENDIENTE)
            ->whereNull('id_nutriologo')
            ->orderByDesc('created_at')
            ->get();

        $mias = Cita::with('padre')
            ->where('id_nutriologo', $uid)
            ->orderByDesc('created_at')
            ->get();

        $proximas = Cita::with('padre')
            ->where('id_nutriologo', $uid)
            ->where('fecha_preferida', '>=', today())
            ->where('estado', '!=', Cita::ESTADO_CANCELADA)
            ->orderBy('fecha_preferida')
            ->take(10)
            ->get();

        return view('nutriologo.citas.index', compact('pendientes', 'mias', 'proximas'));
    }

    public function agenda(Request $request)
    {
        $uid = Auth::id();
        $month = $request->filled('mes')
            ? \Carbon\Carbon::createFromFormat('Y-m', $request->string('mes'))->startOfMonth()
            : now()->startOfMonth();

        $inicio = $month->copy()->startOfMonth();
        $fin = $month->copy()->endOfMonth();

        $citasMes = Cita::with('padre')
            ->where('id_nutriologo', $uid)
            ->whereBetween('fecha_preferida', [$inicio, $fin])
            ->where('estado', '!=', Cita::ESTADO_CANCELADA)
            ->get()
            ->groupBy(fn (Cita $c) => $c->fecha_preferida->format('Y-m-d'));

        $recordatorios = Cita::with('padre')
            ->where('id_nutriologo', $uid)
            ->where('fecha_preferida', '>=', today())
            ->whereIn('estado', [Cita::ESTADO_ASIGNADA, Cita::ESTADO_CONFIRMADA])
            ->orderBy('fecha_preferida')
            ->take(15)
            ->get();

        return view('nutriologo.citas.agenda', compact('month', 'citasMes', 'recordatorios'));
    }

    public function tomar(Cita $cita)
    {
        if ($cita->estado !== Cita::ESTADO_PENDIENTE || $cita->id_nutriologo !== null) {
            return back()->with('error', 'Esta solicitud ya no está disponible.');
        }

        $cita->update([
            'id_nutriologo' => Auth::id(),
            'estado' => Cita::ESTADO_ASIGNADA,
        ]);

        return back()->with('success', 'Has tomado esta cita. Contacta al padre para confirmar fecha y hora.');
    }

    public function confirmar(Cita $cita)
    {
        if ((int) $cita->id_nutriologo !== (int) Auth::id()) {
            abort(403);
        }

        $cita->update(['estado' => Cita::ESTADO_CONFIRMADA]);

        return back()->with('success', 'Cita marcada como confirmada.');
    }
}
