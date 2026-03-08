<?php

namespace App\Http\Controllers\Nutriologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index()
    {
        return view('nutriologo.reportes.index');
    }

    public function show($id)
    {
        return view('nutriologo.reportes.show', compact('id'));
    }
}
