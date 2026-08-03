@extends('layouts.app')

@section('title', 'Gestión de Usuarios - Administrador')

@section('page-title', 'Gestión de Usuarios')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <!-- Formulario para Crear Usuario -->
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 mb-6 border border-slate-200/80 dark:border-slate-800" id="createUserSection" style="display: none;">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-100">Crear Nuevo Usuario</h3>
            <button type="button" onclick="toggleUserForm()" class="text-gray-500 dark:text-slate-400 hover:text-gray-800 dark:hover:text-white p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="createUserForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Nombre</label>
                <input type="text" name="nombre" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Apellido Paterno</label>
                <input type="text" name="apellido_paterno" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Apellido Materno (Opcional)</label>
                <input type="text" name="apellido_materno" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Contraseña</label>
                <input type="password" name="contrasena" required minlength="8" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
                <small class="text-gray-500 dark:text-slate-400 text-xs">Mínimo 8 caracteres, incluyendo mayúsculas, minúsculas y números</small>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Rol</label>
                <select name="rol" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
                    <option value="">Seleccionar rol...</option>
                    <option value="admin">Administrador</option>
                    <option value="nutriologo">Nutriólogo</option>
                </select>
            </div>
            <div class="md:col-span-2 flex justify-end space-x-3">
                <button type="button" onclick="toggleUserForm()" class="px-6 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-800">Cancelar</button>
                <button type="submit" class="px-6 py-2 bg-green-600 dark:bg-emerald-600 text-white rounded-lg hover:bg-green-700 dark:hover:bg-emerald-500">
                    <i class="fas fa-save mr-2"></i>Crear Usuario
                </button>
            </div>
            <div id="userFormMessage" class="md:col-span-2"></div>
        </form>
    </div>

    <!-- Búsqueda y Filtros -->
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 mb-6 border border-slate-200/80 dark:border-slate-800">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
            <div class="flex-1 min-w-0">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 dark:text-slate-500"></i>
                    <input type="text" id="searchInput" placeholder="Buscar usuario..." class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 placeholder:text-gray-500 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
            <select id="roleFilter" class="px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100">
                <option value="">Todos los roles</option>
                <option value="admin">Administrador</option>
                <option value="nutriologo">Nutriólogo</option>
                <option value="padre">Padre</option>
            </select>
            <button type="button" onclick="toggleUserForm()" class="px-6 py-2 bg-green-600 dark:bg-emerald-600 text-white rounded-lg hover:bg-green-700 dark:hover:bg-emerald-500">
                <i class="fas fa-plus mr-2"></i>Nuevo Usuario
            </button>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
            <thead class="bg-slate-100 dark:bg-slate-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Acciones</th>
                </tr>
            </thead>
            <tbody id="usersTableBody" class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-800">
                @forelse($usuarios as $usuario)
                <tr class="user-row hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors" data-name="{{ strtolower($usuario->nombre . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno) }}" data-email="{{ strtolower($usuario->email) }}" data-rol="{{ $usuario->rol }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3
                                @if($usuario->rol === 'admin') bg-purple-100 dark:bg-violet-900/50
                                @elseif($usuario->rol === 'nutriologo') bg-green-100 dark:bg-emerald-900/50
                                @else bg-yellow-100 dark:bg-amber-900/50
                                @endif">
                                <i class="fas 
                                    @if($usuario->rol === 'admin') fa-user-shield text-purple-600 dark:text-violet-300
                                    @elseif($usuario->rol === 'nutriologo') fa-user-md text-green-600 dark:text-emerald-300
                                    @else fa-user text-yellow-700 dark:text-amber-300
                                    @endif"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-slate-100">
                                    {{ $usuario->nombre }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-200">{{ $usuario->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ring-1 ring-black/5 dark:ring-white/10
                            @if($usuario->rol === 'admin') bg-purple-100 dark:bg-violet-950/60 text-purple-900 dark:text-violet-100
                            @elseif($usuario->rol === 'nutriologo') bg-green-100 dark:bg-emerald-950/60 text-green-900 dark:text-emerald-100
                            @else bg-yellow-100 dark:bg-amber-950/60 text-yellow-900 dark:text-amber-50
                            @endif">
                            @if($usuario->rol === 'admin') Administrador
                            @elseif($usuario->rol === 'nutriologo') Nutriólogo
                            @else Padre
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-emerald-950/55 text-green-900 dark:text-emerald-100 ring-1 ring-black/5 dark:ring-white/10">Activo</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.usuarios.edit', $usuario->id_usuario) }}" class="text-blue-600 dark:text-sky-400 hover:text-blue-800 dark:hover:text-sky-300 mr-3">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-slate-400">No hay usuarios registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script>
    // Toggle formulario de creación
    function toggleUserForm() {
        const section = document.getElementById('createUserSection');
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
        if (section.style.display === 'none') {
            document.getElementById('createUserForm').reset();
            document.getElementById('userFormMessage').innerHTML = '';
        }
    }

    // Búsqueda automática
    document.getElementById('searchInput').addEventListener('input', function() {
        filterUsers();
    });

    document.getElementById('roleFilter').addEventListener('change', function() {
        filterUsers();
    });

    function filterUsers() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const roleFilter = document.getElementById('roleFilter').value;
        const rows = document.querySelectorAll('.user-row');

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const email = row.getAttribute('data-email');
            const rol = row.getAttribute('data-rol');

            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesRole = !roleFilter || rol === roleFilter;

            if (matchesSearch && matchesRole) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Manejo del formulario de creación
    document.getElementById('createUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const messageDiv = document.getElementById('userFormMessage');
        messageDiv.innerHTML = '<div class="p-3 bg-yellow-100 text-yellow-800 rounded-lg">Creando usuario...</div>';
        
        fetch('{{ route("admin.usuarios.store") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.innerHTML = '<div class="p-3 bg-green-100 text-green-800 rounded-lg">' + data.message + '</div>';
                this.reset();
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                let errorMsg = data.message || 'Error al crear el usuario';
                if (data.errors && data.errors.length > 0) {
                    errorMsg += ': ' + data.errors.join(', ');
                }
                messageDiv.innerHTML = '<div class="p-3 bg-red-100 text-red-800 rounded-lg">' + errorMsg + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.innerHTML = '<div class="p-3 bg-red-100 text-red-800 rounded-lg">No se pudo completar la acción. Inténtalo de nuevo.</div>';
        });
    });
</script>
@endpush

