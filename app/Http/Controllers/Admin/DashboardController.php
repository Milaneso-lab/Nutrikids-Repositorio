<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

        return view('admin.dashboard', compact(
            'totalUsuarios',
            'totalNutriologos',
            'totalPadres',
            'totalAdmins',
            'ultimosUsuarios'
        ));
    }
}
