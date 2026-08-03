@extends('layouts.app')

@section('title', 'Gestión de Contenido - Administrador')

@section('page-title', 'Gestión de Contenido')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <!-- Tabs para las secciones -->
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md mb-6 border border-slate-200/80 dark:border-slate-800">
        <div class="border-b border-gray-200 dark:border-slate-700">
            <nav class="flex -mb-px">
                <button type="button" onclick="showSection('contactos')" id="tab-contactos" class="tab-button active px-6 py-4 text-sm font-medium text-green-600 dark:text-emerald-400 border-b-2 border-green-600 dark:border-emerald-500 bg-slate-50/50 dark:bg-slate-800/40">
                    <i class="fas fa-envelope mr-2"></i>Contactos
                </button>
                <button type="button" onclick="showSection('comentarios')" id="tab-comentarios" class="tab-button px-6 py-4 text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition">
                    <i class="fas fa-comment mr-2"></i>Comentarios
                </button>
                <button type="button" onclick="showSection('discusiones')" id="tab-discusiones" class="tab-button px-6 py-4 text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition">
                    <i class="fas fa-comments mr-2"></i>Discusiones
                </button>
            </nav>
        </div>
    </div>

    <!-- Sección de Contactos -->
    <div id="section-contactos" class="content-section">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100">Formulario de CONTÁCTANOS</h3>
                <p class="text-sm text-gray-600 dark:text-slate-400">Mensajes recibidos desde el formulario de contacto</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Mensaje</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-800">
                        @forelse($contactos as $contacto)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-slate-100">
                                {{ $contacto->nombre }} {{ $contacto->apellido }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-200">{{ $contacto->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-slate-200">
                                <div class="max-w-xs truncate">{{ $contacto->mensaje }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                {{ $contacto->created_at ? $contacto->created_at->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button type="button" onclick="responderContacto({{ $contacto->id_contacto }})" class="text-green-600 dark:text-emerald-400 hover:text-green-800 dark:hover:text-emerald-300 mr-3">
                                    <i class="fas fa-reply"></i> Responder
                                </button>
                                <button type="button" onclick="eliminarContacto({{ $contacto->id_contacto }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-slate-400">No hay contactos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sección de Comentarios -->
    <div id="section-comentarios" class="content-section" style="display: none;">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100">Formulario de COMENTARIOS</h3>
                <p class="text-sm text-gray-600 dark:text-slate-400">Comentarios publicados por los padres</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Comentario</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-800">
                        @forelse($comentarios as $comentario)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-slate-100">
                                {{ $comentario->nombre }} {{ $comentario->apellido }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-slate-200">
                                <div class="max-w-md">{{ $comentario->comentario }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                {{ $comentario->fecha_comentario ? \Carbon\Carbon::parse($comentario->fecha_comentario)->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button type="button" onclick="eliminarComentario({{ $comentario->id_comentario }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-slate-400">No hay comentarios registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sección de Discusiones -->
    <div id="section-discusiones" class="content-section" style="display: none;">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden border border-slate-200/80 dark:border-slate-800">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-slate-100">Formulario de DISCUSIONES</h3>
                <p class="text-sm text-gray-600 dark:text-slate-400">Discusiones creadas por los padres</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Tema</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-800">
                        @forelse($discusiones as $discusion)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-slate-100">
                                {{ $discusion->tema }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-slate-200">
                                <div class="max-w-md">{{ $discusion->descripcion }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                {{ $discusion->fecha_creacion ? \Carbon\Carbon::parse($discusion->fecha_creacion)->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button type="button" onclick="eliminarDiscusion({{ $discusion->id_discusion }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-slate-400">No hay discusiones registradas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para responder contacto -->
    <div id="modalResponder" class="fixed inset-0 bg-gray-600/60 dark:bg-slate-950/70 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-xl p-6 max-w-md w-full mx-4 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-100">Responder Contacto</h3>
                <button type="button" onclick="cerrarModal()" class="text-gray-500 dark:text-slate-400 hover:text-gray-800 dark:hover:text-white p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="formResponder">
                <input type="hidden" id="contactoId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Respuesta</label>
                    <textarea id="respuestaTexto" rows="5" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-green-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="cerrarModal()" class="px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-800">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 dark:bg-emerald-600 text-white rounded-lg hover:bg-green-700 dark:hover:bg-emerald-500">
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
            btn.classList.remove('active', 'text-green-600', 'border-green-600', 'border-b-2', 'dark:text-emerald-400', 'dark:border-emerald-500', 'bg-slate-50/50', 'dark:bg-slate-800/40');
            btn.classList.add('text-gray-500', 'dark:text-slate-400');
        });
        
        // Mostrar la sección seleccionada
        document.getElementById('section-' + section).style.display = 'block';
        
        // Activar el tab correspondiente
        const tab = document.getElementById('tab-' + section);
        tab.classList.add('active', 'text-green-600', 'border-b-2', 'border-green-600', 'dark:text-emerald-400', 'dark:border-emerald-500', 'bg-slate-50/50', 'dark:bg-slate-800/40');
        tab.classList.remove('text-gray-500', 'dark:text-slate-400');
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
            alert('No se pudo completar la acción. Inténtalo de nuevo.');
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
    .dark .tab-button.active {
        color: rgb(52 211 153);
        border-bottom-color: rgb(16 185 129);
    }
</style>
@endpush

