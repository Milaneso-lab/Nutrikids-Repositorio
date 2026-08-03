<?php

namespace App\Http\Middleware;

use App\Services\Auth\LoginService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function __construct(private LoginService $loginService)
    {
    }

    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! Auth::check()) {
            $intended = $request->is('admin*', 'nutriologo*')
                ? route('acceso')
                : route('login');

            return redirect()->to($intended)->with('error', 'Debes iniciar sesión para acceder a esta página.');
        }

        $user = Auth::user();

        if (! $user->rol || $user->rol !== $role) {
            $home = $this->loginService->homeRouteForRole($user->rol);
            $flask = rtrim((string) env('FLASK_PUBLIC_URL', ''), '/');

            if ($user->rol === 'padre' && $flask !== '') {
                return redirect()->away($flask.'/portal')
                    ->with('error', 'No tienes permisos para acceder a esta sección.');
            }

            if (in_array($home, ['admin.dashboard', 'nutriologo.dashboard'], true)) {
                return redirect()->route($home)
                    ->with('error', 'No tienes permisos para acceder a esta sección.');
            }

            return redirect()->route('index')
                ->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
