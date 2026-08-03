@php
    $navLinkClass = 'flex items-center space-x-3 px-4 py-3 rounded-lg transition text-white';
    $activeClass = 'bg-green-500 text-white shadow-sm';
    $inactiveClass = 'text-white/95 hover:text-white hover:bg-white/25 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60';
@endphp

<a href="{{ route('nutriologo.dashboard') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('nutriologo.dashboard') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-home"></i>
    <span>Dashboard</span>
</a>

<a href="{{ route('nutriologo.citas.agenda') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('nutriologo.citas.agenda') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-calendar-alt"></i>
    <span>Agenda</span>
</a>

<a href="{{ route('nutriologo.citas.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('nutriologo.citas.index') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-calendar-check"></i>
    <span>Citas</span>
</a>

<a href="{{ route('nutriologo.pacientes.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('nutriologo.pacientes.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-child"></i>
    <span>Pacientes</span>
</a>

<a href="{{ route('nutriologo.evaluaciones.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('nutriologo.evaluaciones.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-clipboard-check"></i>
    <span>Evaluaciones</span>
</a>

<a href="{{ route('nutriologo.menus.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('nutriologo.menus.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-utensils"></i>
    <span>Menús</span>
</a>

<a href="{{ route('nutriologo.recomendaciones.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('nutriologo.recomendaciones.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-comment-medical"></i>
    <span>Recomendaciones</span>
</a>

<a href="{{ route('nutriologo.reportes.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('nutriologo.reportes.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-chart-bar"></i>
    <span>Reportes</span>
</a>

<a href="{{ route('nutriologo.perfil.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('nutriologo.perfil.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-user-md"></i>
    <span>Mi perfil</span>
</a>
