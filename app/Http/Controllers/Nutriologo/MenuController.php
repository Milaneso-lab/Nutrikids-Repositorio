<?php

namespace App\Http\Controllers\Nutriologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        return view('nutriologo.menus.index');
    }

    public function create()
    {
        return view('nutriologo.menus.create');
    }

    public function edit($id)
    {
        return view('nutriologo.menus.edit', compact('id'));
    }
}
