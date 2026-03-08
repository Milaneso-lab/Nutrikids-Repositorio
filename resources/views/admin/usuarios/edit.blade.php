@extends('layouts.app')

@section('title', 'Editar Usuario - Administrador')

@section('page-title', 'Editar Usuario')

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
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Editar Usuario</h3>
            <a href="{{ route('admin.usuarios.index') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Volver a Usuarios
            </a>
        </div>

        <form id="editUserForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                <input type="text" name="nombre" value="{{ $usuario->nombre }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Apellido Paterno</label>
                <input type="text" name="apellido_paterno" value="{{ $usuario->apellido_paterno }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Apellido Materno (Opcional)</label>
                <input type="text" name="apellido_materno" value="{{ $usuario->apellido_materno }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ $usuario->email }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Contraseña (Opcional)</label>
                <input type="password" name="contrasena" minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <small class="text-gray-500 text-xs">Dejar en blanco para mantener la contraseña actual</small>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                <select name="rol" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="admin" {{ $usuario->rol === 'admin' ? 'selected' : '' }}>Administrador</option>
                    <option value="nutriologo" {{ $usuario->rol === 'nutriologo' ? 'selected' : '' }}>Nutriólogo</option>
                    <option value="padre" {{ $usuario->rol === 'padre' ? 'selected' : '' }}>Padre</option>
                </select>
            </div>
            <div class="md:col-span-2 flex justify-between items-center pt-4 border-t border-gray-200">
                <button type="button" onclick="confirmDelete()" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i>Eliminar Usuario
                </button>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.usuarios.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-save mr-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
            <div id="userFormMessage" class="md:col-span-2"></div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const messageDiv = document.getElementById('userFormMessage');
        messageDiv.innerHTML = '<div class="p-3 bg-yellow-100 text-yellow-800 rounded-lg">Actualizando usuario...</div>';
        
        fetch('{{ route("admin.usuarios.update", $usuario->id_usuario) }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.innerHTML = '<div class="p-3 bg-green-100 text-green-800 rounded-lg">' + data.message + '</div>';
                setTimeout(() => {
                    window.location.href = '{{ route("admin.usuarios.index") }}';
                }, 1500);
            } else {
                let errorMsg = data.message || 'Error al actualizar el usuario';
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

    function confirmDelete() {
        if (confirm('¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.')) {
            fetch('{{ route("admin.usuarios.destroy", $usuario->id_usuario) }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-HTTP-Method-Override': 'DELETE'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = '{{ route("admin.usuarios.index") }}';
                } else {
                    alert(data.message || 'Error al eliminar el usuario');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión. Por favor, inténtalo de nuevo.');
            });
        }
    }
</script>
@endpush

