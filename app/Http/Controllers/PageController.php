<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function obesidad()
    {
        return view('Obesidad');
    }

    public function calculadora()
    {
        return view('calculadora');
    }

    public function nutriologos()
    {
        return view('nutriologos');
    }

    public function comentarios()
    {
        return view('Comentarios');
    }

    public function foros()
    {
        return view('Foros');
    }

    public function conocenos()
    {
        return view('conocenos');
    }

    public function login()
    {
        return view('login');
    }

    public function dashboard()
    {
        return view('dashboard');
    }

    public function contacto()
    {
        return view('Contacto');
    }
}
