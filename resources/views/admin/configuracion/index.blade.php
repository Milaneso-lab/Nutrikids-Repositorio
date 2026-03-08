@extends('layouts.app')

@section('title', 'Configuración del Sistema - Administrador')

@section('page-title', 'Configuración del Sistema')

@section('navigation')
    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('admin.usuarios.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-users"></i>
        <span>Usuarios</span>
    </a>
    <a href="{{ route('admin.contenido.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-database"></i>
        <span>Contenido</span>
    </a>
    <a href="{{ route('admin.configuracion.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-green-500 rounded-lg text-white">
        <i class="fas fa-cog"></i>
        <span>Configuración</span>
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Configuración General -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Configuración General</h3>
            <form id="configForm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Sistema</label>
                        <input type="text" name="nombre_sistema" value="NutriKids" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email de Contacto</label>
                        <input type="email" name="email_contacto" value="contacto@nutrikids.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono de Contacto</label>
                        <input type="text" name="telefono_contacto" value="+52 123 456 7890" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Política de Privacidad</label>
                        <textarea name="politica_privacidad" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">En NutriKids respetamos tu privacidad y protegemos tus datos personales...</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Términos y Condiciones</label>
                        <textarea name="terminos_condiciones" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">Al usar NutriKids, aceptas nuestros términos y condiciones...</textarea>
                    </div>
                    <button type="submit" class="w-full px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-save mr-2"></i>Guardar Configuración
                    </button>
                    <div id="configMessage"></div>
                </div>
            </form>
        </div>

        <!-- Logo y Apariencia -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Logo y Apariencia</h3>
            <form id="logoForm" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo Actual</label>
                        <div class="mb-4">
                            <img src="{{ asset('Imagenes/logo.png') }}" alt="Logo" class="h-20 w-auto" onerror="this.style.display='none'">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subir Nuevo Logo</label>
                        <input type="file" name="logo" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <small class="text-gray-500 text-xs">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                    </div>
                    <button type="submit" class="w-full px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-upload mr-2"></i>Subir Logo
                    </button>
                    <div id="logoMessage"></div>
                </div>
            </form>
        </div>

        <!-- Permisos y Roles -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Permisos y Roles</h3>
            <div class="space-y-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">Administrador</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Gestión completa de usuarios</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Gestión de nutriólogos</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Gestión de contenido</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Configuración del sistema</li>
                    </ul>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">Nutriólogo</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Gestión de pacientes</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Crear evaluaciones nutricionales</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Asignar menús</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Generar reportes</li>
                    </ul>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">Padre</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Ver información de sus hijos</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Publicar comentarios</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Participar en discusiones</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Contactar con nutriólogos</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Información del Sistema -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Información del Sistema</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Versión del Sistema</span>
                    <span class="text-sm text-gray-600">1.0.0</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Última Actualización</span>
                    <span class="text-sm text-gray-600">{{ date('d/m/Y') }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Estado del Sistema</span>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Operativo</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Base de Datos</span>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Conectada</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('configForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const messageDiv = document.getElementById('configMessage');
        messageDiv.innerHTML = '<div class="p-3 bg-yellow-100 text-yellow-800 rounded-lg">Guardando configuración...</div>';
        
        fetch('{{ route("admin.configuracion.update") }}', {
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
            } else {
                messageDiv.innerHTML = '<div class="p-3 bg-red-100 text-red-800 rounded-lg">' + (data.message || 'Error al guardar la configuración') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.innerHTML = '<div class="p-3 bg-red-100 text-red-800 rounded-lg">Error de conexión. Por favor, inténtalo de nuevo.</div>';
        });
    });

    document.getElementById('logoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const messageDiv = document.getElementById('logoMessage');
        messageDiv.innerHTML = '<div class="p-3 bg-yellow-100 text-yellow-800 rounded-lg">Subiendo logo...</div>';
        
        fetch('{{ route("admin.configuracion.uploadLogo") }}', {
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
                if (data.url) {
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            } else {
                messageDiv.innerHTML = '<div class="p-3 bg-red-100 text-red-800 rounded-lg">' + (data.message || 'Error al subir el logo') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.innerHTML = '<div class="p-3 bg-red-100 text-red-800 rounded-lg">Error de conexión. Por favor, inténtalo de nuevo.</div>';
        });
    });
</script>
@endpush

