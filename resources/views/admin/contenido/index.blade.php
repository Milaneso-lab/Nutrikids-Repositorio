@extends('layouts.app')

@section('title', 'Gestión de Contenido - Administrador')

@section('page-title', 'Gestión de Contenido')

@section('navigation')
    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('admin.usuarios.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-users"></i>
        <span>Usuarios</span>
    </a>
    <a href="{{ route('admin.contenido.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-green-500 rounded-lg text-white">
        <i class="fas fa-database"></i>
        <span>Contenido</span>
    </a>
    <a href="{{ route('admin.configuracion.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-cog"></i>
        <span>Configuración</span>
    </a>
@endsection

@section('content')
    <!-- Tabs para las secciones -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button onclick="showSection('contactos')" id="tab-contactos" class="tab-button active px-6 py-4 text-sm font-medium text-green-600 border-b-2 border-green-600">
                    <i class="fas fa-envelope mr-2"></i>Contactos
                </button>
                <button onclick="showSection('comentarios')" id="tab-comentarios" class="tab-button px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700">
                    <i class="fas fa-comment mr-2"></i>Comentarios
                </button>
                <button onclick="showSection('discusiones')" id="tab-discusiones" class="tab-button px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700">
                    <i class="fas fa-comments mr-2"></i>Discusiones
                </button>
            </nav>
        </div>
    </div>

    <!-- Sección de Contactos -->
    <div id="section-contactos" class="content-section">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Formulario de CONTÁCTANOS</h3>
                <p class="text-sm text-gray-600">Mensajes recibidos desde el formulario de contacto</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mensaje</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($contactos as $contacto)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $contacto->nombre }} {{ $contacto->apellido }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $contacto->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-xs truncate">{{ $contacto->mensaje }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $contacto->created_at ? $contacto->created_at->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="responderContacto({{ $contacto->id_contacto }})" class="text-green-600 hover:text-green-900 mr-3">
                                    <i class="fas fa-reply"></i> Responder
                                </button>
                                <button onclick="eliminarContacto({{ $contacto->id_contacto }})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No hay contactos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sección de Comentarios -->
    <div id="section-comentarios" class="content-section" style="display: none;">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Formulario de COMENTARIOS</h3>
                <p class="text-sm text-gray-600">Comentarios publicados por los padres</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comentario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($comentarios as $comentario)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $comentario->nombre }} {{ $comentario->apellido }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-md">{{ $comentario->comentario }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $comentario->fecha_comentario ? \Carbon\Carbon::parse($comentario->fecha_comentario)->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="eliminarComentario({{ $comentario->id_comentario }})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay comentarios registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sección de Discusiones -->
    <div id="section-discusiones" class="content-section" style="display: none;">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Formulario de DISCUSIONES</h3>
                <p class="text-sm text-gray-600">Discusiones creadas por los padres</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tema</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($discusiones as $discusion)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $discusion->tema }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-md">{{ $discusion->descripcion }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $discusion->fecha_creacion ? \Carbon\Carbon::parse($discusion->fecha_creacion)->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="eliminarDiscusion({{ $discusion->id_discusion }})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay discusiones registradas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para responder contacto -->
    <div id="modalResponder" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Responder Contacto</h3>
                <button onclick="cerrarModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="formResponder">
                <input type="hidden" id="contactoId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Respuesta</label>
                    <textarea id="respuestaTexto" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="cerrarModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-paper-plane mr-2"></i>Enviar Respuesta
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showSection(section) {
        // Ocultar todas las secciones
        document.querySelectorAll('.content-section').forEach(sec => {
            sec.style.display = 'none';
        });
        
        // Ocultar todos los tabs activos
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active', 'text-green-600', 'border-green-600');
            btn.classList.add('text-gray-500');
        });
        
        // Mostrar la sección seleccionada
        document.getElementById('section-' + section).style.display = 'block';
        
        // Activar el tab correspondiente
        const tab = document.getElementById('tab-' + section);
        tab.classList.add('active', 'text-green-600', 'border-b-2', 'border-green-600');
        tab.classList.remove('text-gray-500');
    }

    function responderContacto(id) {
        document.getElementById('contactoId').value = id;
        document.getElementById('modalResponder').classList.remove('hidden');
        document.getElementById('modalResponder').classList.add('flex');
    }

    function cerrarModal() {
        document.getElementById('modalResponder').classList.add('hidden');
        document.getElementById('modalResponder').classList.remove('flex');
        document.getElementById('formResponder').reset();
    }

    document.getElementById('formResponder').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('contactoId').value;
        const respuesta = document.getElementById('respuestaTexto').value;
        
        fetch(`/admin/contenido/contactos/${id}/responder`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ respuesta: respuesta }),
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                cerrarModal();
            } else {
                alert(data.message || 'Error al enviar la respuesta');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión. Por favor, inténtalo de nuevo.');
        });
    });

    function eliminarContacto(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este contacto?')) {
            fetch(`/admin/contenido/contactos/${id}/eliminar`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al eliminar el contacto');
                }
            });
        }
    }

    function eliminarComentario(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este comentario?')) {
            fetch(`/admin/contenido/comentarios/${id}/eliminar`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al eliminar el comentario');
                }
            });
        }
    }

    function eliminarDiscusion(id) {
        if (confirm('¿Estás seguro de que deseas eliminar esta discusión?')) {
            fetch(`/admin/contenido/discusiones/${id}/eliminar`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al eliminar la discusión');
                }
            });
        }
    }
</script>
<style>
    .tab-button.active {
        border-bottom: 2px solid #16a34a;
        color: #16a34a;
    }
</style>
@endpush

