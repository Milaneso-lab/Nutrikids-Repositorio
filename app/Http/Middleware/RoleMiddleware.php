<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder a esta página.');
        }

        $user = Auth::user();
        
        // Si el usuario no tiene rol o no coincide, redirigir
        if (!$user->rol || $user->rol !== $role) {
            return redirect()->route('index')->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}


