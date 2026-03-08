<?php

namespace App\Http\Controllers\Nutriologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    public function index()
    {
        return view('nutriologo.pacientes.index');
    }

    public function create()
    {
        return view('nutriologo.pacientes.create');
    }

    public function show($id)
    {
        return view('nutriologo.pacientes.show', compact('id'));
    }

    public function edit($id)
    {
        return view('nutriologo.pacientes.edit', compact('id'));
    }
}
