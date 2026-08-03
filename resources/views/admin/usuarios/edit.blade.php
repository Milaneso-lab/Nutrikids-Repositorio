@extends('layouts.app')

@section('title', 'Editar Usuario - Administrador')

@section('page-title', 'Editar Usuario')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800 text-gray-900 dark:text-slate-100">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-100">Editar Usuario</h3>
            <a href="{{ route('admin.usuarios.index') }}" class="text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white">
                <i class="fas fa-arrow-left mr-2"></i>Volver a Usuarios
            </a>
        </div>

        <form id="editUserForm" data-sin-bloqueo class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Nombre</label>
                <input type="text" name="nombre" value="{{ $usuario->nombre }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Apellido Paterno</label>
                <input type="text" name="apellido_paterno" value="{{ $usuario->apellido_paterno }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Apellido Materno (Opcional)</label>
                <input type="text" name="apellido_materno" value="{{ $usuario->apellido_materno }}" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Email</label>
                <input type="email" name="email" value="{{ $usuario->email }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Nueva Contraseña (Opcional)</label>
                <input type="password" name="contrasena" minlength="8" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
                <small class="text-gray-500 dark:text-slate-400 text-xs">Dejar en blanco para mantener la contraseña actual</small>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Rol</label>
                <select name="rol" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
                    <option value="admin" {{ $usuario->rol === 'admin' ? 'selected' : '' }}>Administrador</option>
                    <option value="nutriologo" {{ $usuario->rol === 'nutriologo' ? 'selected' : '' }}>Nutriólogo</option>
                    <option value="padre" {{ $usuario->rol === 'padre' ? 'selected' : '' }}>Padre</option>
                </select>
            </div>
            <div class="md:col-span-2 flex justify-between items-center pt-4 border-t border-gray-200 dark:border-slate-700">
                <button type="button" onclick="confirmDelete()" class="px-6 py-2 bg-red-600 dark:bg-red-700 text-white rounded-lg hover:bg-red-700 dark:hover:bg-red-600">
                    <i class="fas fa-trash mr-2"></i>Eliminar Usuario
                </button>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.usuarios.index') }}" class="px-6 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-800">Cancelar</a>
                    <button type="submit" class="px-6 py-2 bg-green-600 dark:bg-emerald-600 text-white rounded-lg hover:bg-green-700 dark:hover:bg-emerald-500">
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
    const formularioEdicion = document.getElementById('editUserForm');
    const botonGuardar = formularioEdicion.querySelector('button[type="submit"]');
    const textoBotonGuardar = botonGuardar.innerHTML;

    function bloquearGuardado(bloquear) {
        botonGuardar.disabled = bloquear;
        botonGuardar.classList.toggle('opacity-60', bloquear);
        botonGuardar.classList.toggle('cursor-not-allowed', bloquear);
        botonGuardar.innerHTML = bloquear
            ? '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...'
            : textoBotonGuardar;
    }

    formularioEdicion.addEventListener('submit', function(e) {
        e.preventDefault();
        if (botonGuardar.disabled) return;

        const formData = new FormData(this);
        const messageDiv = document.getElementById('userFormMessage');
        bloquearGuardado(true);
        messageDiv.innerHTML = '<div class="p-3 bg-yellow-100 text-yellow-800 rounded-lg"><i class="fas fa-spinner fa-spin mr-2"></i>Actualizando usuario...</div>';

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
        .then(async response => {
            if (response.status === 401 || response.status === 419) {
                throw new Error('Tu sesión expiró. Vuelve a iniciar sesión para guardar los cambios.');
            }
            if (response.status === 403) {
                throw new Error('No tienes permisos para modificar este usuario.');
            }
            return { estado: response.status, datos: await response.json() };
        })
        .then(({ datos }) => {
            if (datos.success) {
                messageDiv.innerHTML = '<div class="p-3 bg-green-100 text-green-800 rounded-lg"><i class="fas fa-circle-check mr-2"></i>' + datos.message + '</div>';
                setTimeout(() => {
                    window.location.href = '{{ route("admin.usuarios.index") }}';
                }, 1200);
                return;
            }

            let errorMsg = datos.message || 'No se pudo actualizar el usuario.';
            if (datos.errors && datos.errors.length > 0) {
                errorMsg += ' ' + datos.errors.join(' ');
            }
            messageDiv.innerHTML = '<div class="p-3 bg-red-100 text-red-800 rounded-lg"><i class="fas fa-circle-exclamation mr-2"></i>' + errorMsg + '</div>';
            bloquearGuardado(false);
        })
        .catch(error => {
            const texto = window.NutriKidsMessages ? NutriKidsMessages.fromCatch(error) : 'No se pudo completar la acción. Inténtalo de nuevo.';
            messageDiv.innerHTML = '<div class="p-3 bg-red-100 text-red-800 rounded-lg"><i class="fas fa-circle-exclamation mr-2"></i>' + texto + '</div>';
            bloquearGuardado(false);
        });
    });

    function confirmDelete() {
        const nombre = @json($usuario->nombre.' '.$usuario->apellido_paterno);
        if (!confirm('¿Eliminar a ' + nombre + '? Esta acción no se puede deshacer y se perderán sus registros asociados.')) {
            return;
        }

        const messageDiv = document.getElementById('userFormMessage');
        const botonEliminar = document.querySelector('button[onclick="confirmDelete()"]');
        botonEliminar.disabled = true;
        botonEliminar.classList.add('opacity-60', 'cursor-not-allowed');
        messageDiv.innerHTML = '<div class="p-3 bg-yellow-100 text-yellow-800 rounded-lg"><i class="fas fa-spinner fa-spin mr-2"></i>Eliminando usuario...</div>';

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
        .then(async response => {
            if (response.status === 401 || response.status === 419) {
                throw new Error('Tu sesión expiró. Vuelve a iniciar sesión.');
            }
            if (response.status === 403) {
                throw new Error('No tienes permisos para eliminar usuarios.');
            }
            return response.json();
        })
        .then(datos => {
            if (datos.success) {
                messageDiv.innerHTML = '<div class="p-3 bg-green-100 text-green-800 rounded-lg"><i class="fas fa-circle-check mr-2"></i>' + datos.message + '</div>';
                window.location.href = '{{ route("admin.usuarios.index") }}';
                return;
            }
            messageDiv.innerHTML = '<div class="p-3 bg-red-100 text-red-800 rounded-lg"><i class="fas fa-circle-exclamation mr-2"></i>' + (datos.message || 'No se pudo eliminar el usuario.') + '</div>';
            botonEliminar.disabled = false;
            botonEliminar.classList.remove('opacity-60', 'cursor-not-allowed');
        })
        .catch(error => {
            const texto = window.NutriKidsMessages ? NutriKidsMessages.fromCatch(error) : 'No se pudo completar la acción. Inténtalo de nuevo.';
            messageDiv.innerHTML = '<div class="p-3 bg-red-100 text-red-800 rounded-lg"><i class="fas fa-circle-exclamation mr-2"></i>' + texto + '</div>';
            botonEliminar.disabled = false;
            botonEliminar.classList.remove('opacity-60', 'cursor-not-allowed');
        });
    }
</script>
@endpush

