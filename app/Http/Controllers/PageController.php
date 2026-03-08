<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        return view('auth.login');
    }

    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder al dashboard.');
        }
        $user = Auth::user();
        $rol = $user->rol ?? 'padre';
        if ($rol === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($rol === 'nutriologo') {
            return redirect()->route('nutriologo.dashboard');
        }
        return view('dashboard');
    }

    public function contacto()
    {
        return view('Contacto');
    }
}
