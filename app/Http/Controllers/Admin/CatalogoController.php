<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Paciente;

class CatalogoController extends Controller
{
    public function index()
    {
        $stats = [
            'menus' => Menu::count(),
            'pacientes' => Paciente::count(),
        ];

        return view('admin.catalogos.index', compact('stats'));
    }
}
