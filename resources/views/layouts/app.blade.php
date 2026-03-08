<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NutriKids')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-green-600 to-green-700 text-white shadow-lg">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <img src="{{ asset('Imagenes/nukidslofgo-Photoroom (1).png') }}" alt="NutriKids" class="h-10 w-auto">
                    <h1 class="text-xl font-bold">NutriKids</h1>
                </div>
                <nav class="space-y-2">
                    @yield('navigation')
                </nav>
            </div>
            <div class="absolute bottom-0 w-64 p-4 border-t border-green-500">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold">{{ Auth::user()->nombre ?? 'Usuario' }}</p>
                        <p class="text-xs text-green-200">{{ Auth::user()->rol ?? 'Rol' }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-center text-sm text-green-200 hover:text-white">
                        <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4 flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                    <div class="flex items-center space-x-4">
                        <button class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-full">
                            <i class="fas fa-bell text-xl"></i>
                        </button>
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-green-600"></i>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

