<?php

namespace App\Http\Controllers\Nutriologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EvaluacionController extends Controller
{
    public function index()
    {
        return view('nutriologo.evaluaciones.index');
    }

    public function create()
    {
        return view('nutriologo.evaluaciones.create');
    }

    public function edit($id)
    {
        return view('nutriologo.evaluaciones.edit', compact('id'));
    }
}
