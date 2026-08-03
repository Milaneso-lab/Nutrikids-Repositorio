@php
    $navLinkClass = 'flex items-center space-x-3 px-4 py-3 rounded-lg transition text-white';
    $activeClass = 'bg-green-500 text-white shadow-sm';
    $inactiveClass = 'text-white/95 hover:text-white hover:bg-white/25 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60';
@endphp

<a href="{{ route('admin.dashboard') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('admin.dashboard') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-home"></i>
    <span>Dashboard</span>
</a>

<a href="{{ route('admin.usuarios.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('admin.usuarios.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-users"></i>
    <span>Usuarios</span>
</a>

<a href="{{ route('admin.roles.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('admin.roles.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-user-shield"></i>
    <span>Roles</span>
</a>

<a href="{{ route('admin.permisos.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('admin.permisos.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-key"></i>
    <span>Permisos</span>
</a>

<a href="{{ route('admin.nutriologos.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('admin.nutriologos.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-user-md"></i>
    <span>Nutriólogos</span>
</a>

<a href="{{ route('admin.citas.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('admin.citas.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-calendar-check"></i>
    <span>Citas</span>
</a>

<a href="{{ route('admin.instituciones.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('admin.instituciones.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-school"></i>
    <span>Instituciones</span>
</a>

<a href="{{ route('admin.catalogos.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('admin.catalogos.*') || request()->routeIs('admin.contenido.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-database"></i>
    <span>Catálogos</span>
</a>

<a href="{{ route('admin.estadisticas.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('admin.estadisticas.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-chart-pie"></i>
    <span>Estadísticas</span>
</a>

<a href="{{ route('admin.auditoria.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('admin.auditoria.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-clipboard-list"></i>
    <span>Auditoría</span>
</a>

<a href="{{ route('admin.bitacora.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('admin.bitacora.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-book"></i>
    <span>Bitácora</span>
</a>

<a href="{{ route('admin.configuracion.index') }}"
   class="{{ $navLinkClass }} {{ request()->routeIs('admin.configuracion.*') ? $activeClass : $inactiveClass }}">
    <i class="fas fa-cog"></i>
    <span>Configuración</span>
</a>
