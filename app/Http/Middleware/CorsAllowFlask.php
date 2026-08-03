<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Permite que el login en Flask llame a POST /IniciarSesion con credenciales (distinto puerto que Laravel).
 * Docker: Flask :5000 → Laravel :8080. Incluir 127.0.0.1 y localhost (orígenes distintos para CORS).
 */
class CorsAllowFlask
{
    private function allowedOrigins(): array
    {
        $extra = env('FLASK_CORS_ORIGINS', '');
        $defaults = [
            'http://127.0.0.1:5000',
            'http://localhost:5000',
            'http://127.0.0.1:5001',
            'http://localhost:5001',
        ];
        $flask = rtrim((string) env('FLASK_PUBLIC_URL', ''), '/');
        if ($flask !== '') {
            $defaults[] = $flask;
        }
        if ($extra !== '') {
            foreach (explode(',', $extra) as $o) {
                $o = trim($o);
                if ($o !== '') {
                    $defaults[] = $o;
                }
            }
        }

        return array_values(array_unique($defaults));
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->path() !== 'IniciarSesion') {
            return $next($request);
        }

        $origin = $request->header('Origin');
        $allowed = $this->allowedOrigins();

        if ($request->isMethod('OPTIONS')) {
            $response = response('', 204);
            if ($origin && in_array($origin, $allowed, true)) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
                $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
                $response->headers->set(
                    'Access-Control-Allow-Headers',
                    'Content-Type, Accept, X-Requested-With, X-CSRF-TOKEN'
                );
            }

            return $response;
        }

        $response = $next($request);

        if ($origin && in_array($origin, $allowed, true)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
            $response->headers->set(
                'Access-Control-Allow-Headers',
                'Content-Type, Accept, X-Requested-With, X-CSRF-TOKEN'
            );
        }

        return $response;
    }
}
