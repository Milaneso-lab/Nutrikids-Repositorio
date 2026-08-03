<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginService
{
    public function attempt(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();
        $hash = $user ? (string) ($user->getRawOriginal('contrasena') ?? '') : '';

        if (! $user || $hash === '' || ! password_verify($password, $hash)) {
            return null;
        }

        return $user;
    }

    public function loginUser(User $user): void
    {
        Auth::login($user);
        request()->session()->regenerate();
    }

    public function redirectUrlFor(User $user, Request $request): string
    {
        $rol = $user->rol ?? 'padre';
        $flaskPublic = rtrim((string) config('services.flask.public_url', env('FLASK_PUBLIC_URL', '')), '/');

        if ($rol === 'padre') {
            return $flaskPublic !== '' ? $flaskPublic.'/portal' : route('index');
        }

        $routeName = config("nutrikids.redirects.{$rol}", 'index');

        if ($routeName === 'flask.portal') {
            return $flaskPublic !== '' ? $flaskPublic.'/portal' : route('index');
        }

        $path = route($routeName, [], false);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return $request->getSchemeAndHttpHost().(str_starts_with($path, '/') ? $path : '/'.$path);
    }

    public function homeRouteForRole(?string $rol): string
    {
        return match ($rol) {
            'admin' => 'admin.dashboard',
            'nutriologo' => 'nutriologo.dashboard',
            'padre' => 'index',
            default => 'index',
        };
    }
}
