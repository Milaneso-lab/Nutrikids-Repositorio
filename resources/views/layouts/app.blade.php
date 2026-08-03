<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NutriKids')</title>
    <script>
        (function () {
            try {
                var t = localStorage.getItem('nutrikids-theme');
                if (t === 'dark') {
                    document.documentElement.classList.add('dark');
                } else if (t === 'light') {
                    document.documentElement.classList.remove('dark');
                } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['"Source Sans 3"', 'ui-sans-serif', 'system-ui', 'sans-serif'] }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/user-messages.js') }}" defer></script>
    @stack('styles')
    {{-- Evita texto claro heredado sobre tarjetas que siguen en blanco en modo oscuro --}}
    <style>
        .dark main .bg-white:not([class*="dark:bg-"]) { color: rgb(15 23 42); }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 font-sans antialiased text-slate-800 min-h-full">
    <!-- Sidebar -->
    <div class="flex h-screen bg-slate-50 dark:bg-slate-950">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-emerald-800 to-emerald-900 dark:from-slate-900 dark:to-slate-950 text-white shadow-lg border-r border-emerald-900/40 dark:border-slate-800">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <img src="{{ asset('Imagenes/nukidslofgo-Photoroom (1).png') }}" alt="NutriKids" class="h-10 w-auto">
                    <h1 class="text-xl font-bold text-white">NutriKids</h1>
                </div>
                <nav class="space-y-2">
                    @yield('navigation')
                </nav>
            </div>
            <div class="absolute bottom-0 w-64 p-4 border-t border-emerald-600/60 dark:border-slate-700/80 bg-emerald-900/20 dark:bg-slate-900/40">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-emerald-600 dark:bg-emerald-700 rounded-full flex items-center justify-center shrink-0">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->nombre ?? 'Usuario' }}</p>
                        <p class="text-xs text-emerald-100 dark:text-emerald-200/90 truncate">{{ Auth::user()->rol ?? 'Rol' }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-center text-sm text-emerald-100 hover:text-white hover:underline rounded py-1">
                        <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white dark:bg-slate-900 shadow-sm border-b border-slate-200/80 dark:border-slate-700/80">
                <div class="px-6 py-4 flex items-center justify-between gap-4">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-800 dark:text-slate-100">@yield('page-title', 'Dashboard')</h2>
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        @php
                            $citasAlertUrl = null;
                            if (Auth::check()) {
                                $rol = Auth::user()->rol ?? '';
                                if ($rol === 'admin') {
                                    $citasAlertUrl = route('admin.citas.index');
                                } elseif ($rol === 'nutriologo') {
                                    $citasAlertUrl = route('nutriologo.citas.index');
                                }
                            }
                        @endphp
                        <button type="button" id="nutrikids-theme-toggle" class="p-2 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition" title="Modo claro / oscuro">
                            <i class="fas fa-moon dark:hidden text-lg"></i>
                            <i class="fas fa-sun hidden dark:inline text-lg text-amber-300"></i>
                        </button>
                        @if($citasAlertUrl)
                            <a href="{{ $citasAlertUrl }}" class="p-2 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition" title="Citas">
                                <i class="fas fa-bell text-xl"></i>
                            </a>
                        @else
                            <span class="p-2 text-slate-400 dark:text-slate-600 rounded-full cursor-default" title="Citas">
                                <i class="fas fa-bell text-xl"></i>
                            </span>
                        @endif
                        @php
                            $panelInicioUrl = route('admin.dashboard');
                            if (($u = Auth::user()) && ($u->rol ?? '') === 'nutriologo') {
                                $panelInicioUrl = route('nutriologo.dashboard');
                            }
                        @endphp
                        <a href="{{ $panelInicioUrl }}" class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/60 rounded-full flex items-center justify-center shrink-0 border border-emerald-200/80 dark:border-emerald-700/50 text-emerald-800 dark:text-emerald-200 hover:bg-emerald-200/90 dark:hover:bg-emerald-800/80 transition" title="Ir al panel" aria-label="Ir al panel principal">
                            <i class="fas fa-user"></i>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-300">
                @if(session('success'))
                    <div class="mb-4 flex items-start gap-3 bg-green-100 dark:bg-emerald-950/50 border border-green-400 dark:border-emerald-700 text-green-800 dark:text-emerald-200 px-4 py-3 rounded" role="status" aria-live="polite">
                        <i class="fas fa-circle-check mt-1"></i>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 flex items-start gap-3 bg-red-100 dark:bg-red-950/40 border border-red-400 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded" role="alert" aria-live="assertive">
                        <i class="fas fa-circle-exclamation mt-1"></i>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 flex items-start gap-3 bg-red-100 dark:bg-red-950/40 border border-red-400 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded" role="alert" aria-live="assertive">
                        <i class="fas fa-triangle-exclamation mt-1"></i>
                        <div>
                            <p class="font-semibold">Revisa los datos del formulario:</p>
                            <ul class="list-disc list-inside mt-1 space-y-0.5">
                                @foreach($errors->all() as $mensaje)
                                    <li>{{ $mensaje }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        (function () {
            var btn = document.getElementById('nutrikids-theme-toggle');
            if (!btn) return;
            btn.addEventListener('click', function () {
                var d = document.documentElement.classList.toggle('dark');
                try {
                    localStorage.setItem('nutrikids-theme', d ? 'dark' : 'light');
                } catch (e) {}
            });
        })();
    </script>
    <script>
        // Estado de carga y bloqueo de doble envío para los formularios del panel.
        // Los formularios que se envían por fetch marcan data-sin-bloqueo y se excluyen.
        (function () {
            document.addEventListener('submit', function (evento) {
                var formulario = evento.target;
                if (!(formulario instanceof HTMLFormElement) || formulario.hasAttribute('data-sin-bloqueo')) return;

                if (formulario.dataset.enviando === '1') {
                    evento.preventDefault();
                    return;
                }
                formulario.dataset.enviando = '1';

                var boton = formulario.querySelector('button[type="submit"], input[type="submit"]');
                if (!boton) return;

                var textoOriginal = boton.innerHTML;
                boton.disabled = true;
                boton.classList.add('opacity-60', 'cursor-not-allowed');
                boton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';

                // Si el navegador restaura la página desde caché (botón atrás), rehabilitar.
                window.addEventListener('pageshow', function () {
                    formulario.dataset.enviando = '';
                    boton.disabled = false;
                    boton.classList.remove('opacity-60', 'cursor-not-allowed');
                    boton.innerHTML = textoOriginal;
                });
            }, true);

            // Confirmación antes de acciones destructivas: data-confirmar="mensaje".
            document.addEventListener('click', function (evento) {
                var disparador = evento.target.closest('[data-confirmar]');
                if (!disparador) return;
                if (!window.confirm(disparador.getAttribute('data-confirmar'))) {
                    evento.preventDefault();
                    evento.stopPropagation();
                }
            }, true);
        })();
    </script>
    @stack('scripts')
</body>
</html>

