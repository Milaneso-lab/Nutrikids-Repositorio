<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Comentario;
use App\Models\Contacto;
use App\Models\Discusion;
use App\Models\Evaluacion;
use App\Models\Paciente;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // Obtener estadísticas reales de la base de datos
        $totalUsuarios = User::count();
        $totalNutriologos = User::where('rol', 'nutriologo')->count();
        $totalPadres = User::where('rol', 'padre')->count();
        $totalAdmins = User::where('rol', 'admin')->count();
        
        // Obtener últimos usuarios registrados
        $ultimosUsuarios = User::orderBy('id_usuario', 'desc')
            ->take(5)
            ->get();

        $alertasSistema = collect();

        $citasPendientes = Cita::where('estado', Cita::ESTADO_PENDIENTE)->count();
        if ($citasPendientes > 0) {
            $alertasSistema->push([
                'tipo' => 'warning',
                'titulo' => 'Citas pendientes por asignar',
                'descripcion' => 'Hay '.$citasPendientes.' cita(s) pendientes esperando revisión o asignación.',
                'fecha' => 'Actualizado ahora',
            ]);
        }

        $contactosPendientes = Contacto::count();
        if ($contactosPendientes > 0) {
            $alertasSistema->push([
                'tipo' => 'info',
                'titulo' => 'Mensajes de contacto registrados',
                'descripcion' => 'Hay '.$contactosPendientes.' mensaje(s) almacenados en la bandeja de contenido.',
                'fecha' => 'Disponible para revisión',
            ]);
        }

        $actividadComunidad = Comentario::count() + Discusion::count();
        $alertasSistema->push([
            'tipo' => 'success',
            'titulo' => 'Actividad de la comunidad',
            'descripcion' => 'Se registran '.$actividadComunidad.' interacciones entre comentarios y discusiones.',
            'fecha' => 'Estado actual',
        ]);

        $totalPacientes = Paciente::count();
        $totalEvaluaciones = Evaluacion::count();
        $citasConfirmadas = Cita::where('estado', Cita::ESTADO_CONFIRMADA)->count();

        $accesosRapidos = [
            ['label' => 'Gestionar citas', 'route' => route('admin.citas.index'), 'icon' => 'fa-calendar-check'],
            ['label' => 'Usuarios', 'route' => route('admin.usuarios.index'), 'icon' => 'fa-users'],
            ['label' => 'Nutriólogos', 'route' => route('admin.nutriologos.index'), 'icon' => 'fa-user-md'],
            ['label' => 'Estadísticas', 'route' => route('admin.estadisticas.index'), 'icon' => 'fa-chart-pie'],
            ['label' => 'Auditoría', 'route' => route('admin.auditoria.index'), 'icon' => 'fa-clipboard-list'],
        ];

        return view('admin.dashboard', compact(
            'totalUsuarios',
            'totalNutriologos',
            'totalPadres',
            'totalAdmins',
            'ultimosUsuarios',
            'alertasSistema',
            'totalPacientes',
            'totalEvaluaciones',
            'citasConfirmadas',
            'accesosRapidos'
        ));
    }
}
