@extends('layouts.app')

@section('title', 'Gestión de Usuarios - Administrador')

@section('page-title', 'Gestión de Usuarios')

@section('navigation')
    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('admin.usuarios.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-green-500 rounded-lg text-white">
        <i class="fas fa-users"></i>
        <span>Usuarios</span>
    </a>
    <a href="{{ route('admin.contenido.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-database"></i>
        <span>Contenido</span>
    </a>
    <a href="{{ route('admin.configuracion.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-cog"></i>
        <span>Configuración</span>
    </a>
@endsection

@section('content')
    <!-- Formulario para Crear Usuario -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6" id="createUserSection" style="display: none;">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Crear Nuevo Usuario</h3>
            <button onclick="toggleUserForm()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="createUserForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                <input type="text" name="nombre" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Apellido Paterno</label>
                <input type="text" name="apellido_paterno" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Apellido Materno (Opcional)</label>
                <input type="text" name="apellido_materno" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña</label>
                <input type="password" name="contrasena" required minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <small class="text-gray-500 text-xs">Mínimo 8 caracteres, incluyendo mayúsculas, minúsculas y números</small>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                <select name="rol" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Seleccionar rol...</option>
                    <option value="admin">Administrador</option>
                    <option value="nutriologo">Nutriólogo</option>
                </select>
            </div>
            <div class="md:col-span-2 flex justify-end space-x-3">
                <button type="button" onclick="toggleUserForm()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-save mr-2"></i>Crear Usuario
                </button>
            </div>
            <div id="userFormMessage" class="md:col-span-2"></div>
        </form>
    </div>

    <!-- Búsqueda y Filtros -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex-1 mr-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Buscar usuario..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <select id="roleFilter" class="px-4 py-2 border border-gray-300 rounded-lg mr-4">
                <option value="">Todos los roles</option>
                <option value="admin">Administrador</option>
                <option value="nutriologo">Nutriólogo</option>
                <option value="padre">Padre</option>
            </select>
            <button onclick="toggleUserForm()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-plus mr-2"></i>Nuevo Usuario
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody id="usersTableBody" class="bg-white divide-y divide-gray-200">
                @forelse($usuarios as $usuario)
                <tr class="user-row hover:bg-gray-50" data-name="{{ strtolower($usuario->nombre . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno) }}" data-email="{{ strtolower($usuario->email) }}" data-rol="{{ $usuario->rol }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3
                                @if($usuario->rol === 'admin') bg-purple-100
                                @elseif($usuario->rol === 'nutriologo') bg-green-100
                                @else bg-yellow-100
                                @endif">
                                <i class="fas 
                                    @if($usuario->rol === 'admin') fa-user-shield text-purple-600
                                    @elseif($usuario->rol === 'nutriologo') fa-user-md text-green-600
                                    @else fa-user text-yellow-600
                                    @endif"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $usuario->nombre }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $usuario->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            @if($usuario->rol === 'admin') bg-purple-100 text-purple-800
                            @elseif($usuario->rol === 'nutriologo') bg-green-100 text-green-800
                            @else bg-yellow-100 text-yellow-800
                            @endif">
                            @if($usuario->rol === 'admin') Administrador
                            @elseif($usuario->rol === 'nutriologo') Nutriólogo
                            @else Padre
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.usuarios.edit', $usuario->id_usuario) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No hay usuarios registrados.</td>
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
            messageDiv.innerHTML = '<div class="p-3 bg-red-100 text-red-800 rounded-lg">Error de conexión. Por favor, inténtalo de nuevo.</div>';
        });
    });
</script>
@endpush

