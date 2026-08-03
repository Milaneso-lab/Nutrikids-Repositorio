@extends('layouts.app')

@section('title', 'Configuración del Sistema - Administrador')

@section('page-title', 'Configuración del Sistema')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Configuración General -->
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100 mb-4">Configuración General</h3>
            <form id="configForm" data-sin-bloqueo>
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Nombre del Sistema</label>
                        <input type="text" name="nombre_sistema" value="{{ old('nombre_sistema', $configuracion['nombre_sistema'] ?? '') }}" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Email de Contacto</label>
                        <input type="email" name="email_contacto" value="{{ old('email_contacto', $configuracion['email_contacto'] ?? '') }}" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Teléfono de Contacto</label>
                        <input type="text" name="telefono_contacto" value="{{ old('telefono_contacto', $configuracion['telefono_contacto'] ?? '') }}" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Política de Privacidad</label>
                        <textarea name="politica_privacidad" rows="5" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">{{ old('politica_privacidad', $configuracion['politica_privacidad'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Términos y Condiciones</label>
                        <textarea name="terminos_condiciones" rows="5" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">{{ old('terminos_condiciones', $configuracion['terminos_condiciones'] ?? '') }}</textarea>
                    </div>
                    <button type="submit" class="w-full px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-save mr-2"></i>Guardar Configuración
                    </button>
                    <div id="configMessage"></div>
                </div>
            </form>
        </div>

        <!-- Logo y Apariencia -->
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100 mb-4">Logo y Apariencia</h3>
            <form id="logoForm" data-sin-bloqueo enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Logo Actual</label>
                        <div class="mb-4">
                            <img src="{{ asset('Imagenes/logo.png') }}" alt="Logo" class="h-20 w-auto" onerror="this.style.display='none'">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Subir Nuevo Logo</label>
                        <input type="file" name="logo" accept="image/*" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500">
                        <small class="text-gray-500 dark:text-slate-400 text-xs">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                    </div>
                    <button type="submit" class="w-full px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-upload mr-2"></i>Subir Logo
                    </button>
                    <div id="logoMessage"></div>
                </div>
            </form>
        </div>

        <!-- Permisos y Roles -->
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100 mb-4">Permisos y Roles</h3>
            <div class="space-y-4">
                <div class="p-4 bg-gray-50 dark:bg-slate-800/80 rounded-lg border border-slate-200/60 dark:border-slate-700">
                    <h4 class="font-semibold text-gray-800 dark:text-slate-100 mb-2">Administrador</h4>
                    <ul class="text-sm text-gray-600 dark:text-slate-400 space-y-1">
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Gestión completa de usuarios</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Gestión de nutriólogos</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Gestión de contenido</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Configuración del sistema</li>
                    </ul>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-slate-800/80 rounded-lg border border-slate-200/60 dark:border-slate-700">
                    <h4 class="font-semibold text-gray-800 dark:text-slate-100 mb-2">Nutriólogo</h4>
                    <ul class="text-sm text-gray-600 dark:text-slate-400 space-y-1">
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Gestión de pacientes</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Crear evaluaciones nutricionales</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Asignar menús</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Generar reportes</li>
                    </ul>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-slate-800/80 rounded-lg border border-slate-200/60 dark:border-slate-700">
                    <h4 class="font-semibold text-gray-800 dark:text-slate-100 mb-2">Padre</h4>
                    <ul class="text-sm text-gray-600 dark:text-slate-400 space-y-1">
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Ver información de sus hijos</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Publicar comentarios</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Participar en discusiones</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Contactar con nutriólogos</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Información del Sistema -->
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 border border-slate-200/80 dark:border-slate-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100 mb-4">Información del Sistema</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Versión del Sistema</span>
                    <span class="text-sm text-gray-600 dark:text-slate-400">1.0.0</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Última Actualización</span>
                    <span class="text-sm text-gray-600 dark:text-slate-400">{{ date('d/m/Y') }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Estado del Sistema</span>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Operativo</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function enviarFormularioPanel(formulario, url, cajaMensaje, textoCargando, alTerminarConExito) {
        const boton = formulario.querySelector('button[type="submit"]');
        const textoOriginal = boton.innerHTML;

        function bloquear(activo) {
            boton.disabled = activo;
            boton.classList.toggle('opacity-60', activo);
            boton.classList.toggle('cursor-not-allowed', activo);
            boton.innerHTML = activo ? '<i class="fas fa-spinner fa-spin mr-2"></i>' + textoCargando : textoOriginal;
        }

        function mostrar(tipo, texto) {
            const estilos = tipo === 'ok' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            const icono = tipo === 'ok' ? 'fa-circle-check' : 'fa-circle-exclamation';
            cajaMensaje.innerHTML = '<div class="p-3 ' + estilos + ' rounded-lg"><i class="fas ' + icono + ' mr-2"></i>' + texto + '</div>';
        }

        if (boton.disabled) return;
        bloquear(true);
        cajaMensaje.innerHTML = '<div class="p-3 bg-yellow-100 text-yellow-800 rounded-lg"><i class="fas fa-spinner fa-spin mr-2"></i>' + textoCargando + '</div>';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: new FormData(formulario),
            credentials: 'same-origin'
        })
        .then(async response => {
            if (response.status === 401 || response.status === 419) throw new Error('Tu sesión expiró. Vuelve a iniciar sesión.');
            if (response.status === 403) throw new Error('No tienes permisos para realizar esta acción.');
            return response.json();
        })
        .then(datos => {
            bloquear(false);
            if (datos.success) {
                mostrar('ok', datos.message);
                if (alTerminarConExito) alTerminarConExito(datos);
                return;
            }
            let texto = datos.message || 'No se pudo completar la operación.';
            if (datos.errors && datos.errors.length) texto += ' ' + datos.errors.join(' ');
            mostrar('error', texto);
        })
        .catch(error => {
            bloquear(false);
            mostrar('error', window.NutriKidsMessages ? NutriKidsMessages.fromCatch(error) : 'No se pudo completar la acción. Inténtalo de nuevo.');
        });
    }

    document.getElementById('configForm').addEventListener('submit', function(e) {
        e.preventDefault();
        enviarFormularioPanel(this, '{{ route("admin.configuracion.update") }}', document.getElementById('configMessage'), 'Guardando configuración...');
    });

    document.getElementById('logoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        enviarFormularioPanel(this, '{{ route("admin.configuracion.uploadLogo") }}', document.getElementById('logoMessage'), 'Subiendo logo...', function (datos) {
            if (datos.url) setTimeout(() => location.reload(), 1200);
        });
    });
</script>
@endpush

