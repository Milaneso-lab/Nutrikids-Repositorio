<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NutriKids | Foros</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('CSS/style.css') }}">
    <style>
        /* Estilos para el formulario/* Estilos para el encabezado y menú */
        .encabezado-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px 0;
        }
        
        .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            width: 120px;
            height: auto;
            margin-right: 20px;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }
        
        .menu {
            flex: 1;
            display: flex;
            justify-content: center;
        }
        
        .menu ul {
            display: flex;
            justify-content: center;
            flex-wrap: nowrap;
            padding: 0;
            margin: 0;
            gap: 5px;
            list-style: none;
        }
        
        .menu li {
            margin: 0 5px;
        }
        
        .menu a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            padding: 6px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-size: 14px;
            white-space: nowrap;
        }
        
        .menu a:hover {
            background-color: #f0f0f0;
        }
        
        /* Estilo para la pestaña activa */
        .menu li a[href="{{ route("foros") }}"] {
            background-color: #4CAF50;
            color: white;
        }
        
        .iconos-redes {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }
        
        .iconos-redes img {
            width: 35px;
            height: 35px;
            transition: transform 0.3s ease;
        }
        
        .iconos-redes img:hover {
            transform: scale(1.1);
        }
        
        /* Estilos para la sección de discusiones centrada */
        .discussions-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .discussions-section {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 15px;
        }
        
        .section-header h2 {
            color: #2c3e50;
            font-size: 1.8em;
            margin: 0;
        }
        
        .btn-secondary {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn-secondary:hover {
            background-color: #45a049;
        }
        
        .header-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .btn-refresh {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-refresh:hover {
            background-color: #5a6268;
            transform: rotate(180deg);
        }
        
        .form-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .form-container.hidden {
            display: none;
        }
        
        .form-input, .form-textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-input:focus, .form-textarea:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }
        
        .btn-publish {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn-publish:hover {
            background-color: #45a049;
        }
        
        .discussions-list {
            margin-top: 20px;
        }
        
        .discussion {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-left: 5px solid #4CAF50;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .discussion::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #45a049);
        }
        
        .discussion:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.15);
        }
        
        .discussion h3 {
            color: #2c3e50;
            margin: 0 0 15px 0;
            font-size: 1.4em;
            font-weight: 600;
            line-height: 1.3;
        }
        
        .discussion p {
            color: #34495e;
            line-height: 1.7;
            margin: 0 0 20px 0;
            font-size: 1.05em;
            text-align: justify;
            max-height: 5.1em; /* Limita a 3 líneas aproximadamente */
            overflow: hidden;
            position: relative;
            transition: max-height 0.3s ease;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .discussion p.expanded {
            max-height: none;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .discussion p::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 60px;
            height: 1.7em;
            background: linear-gradient(to right, transparent, #f8f9fa);
            pointer-events: none;
            opacity: 1;
            transition: opacity 0.3s ease;
        }
        
        .discussion p.expanded::after {
            opacity: 0;
        }
        
        .btn-leer-mas-discusion {
            background: none;
            border: none;
            color: #4CAF50;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: bold;
            padding: 5px 0;
            margin-top: 5px;
            text-decoration: underline;
            transition: color 0.3s ease;
        }
        
        .btn-leer-mas-discusion:hover {
            color: #45a049;
        }
        
        .btn-leer-mas-discusion.hidden {
            display: none;
        }
        
        /* Modal para ver discusión completa */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 700px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            border-left: 5px solid #4CAF50;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4CAF50;
        }
        
        .modal-title {
            color: #2c3e50;
            font-size: 1.6em;
            font-weight: 600;
            margin: 0;
            line-height: 1.3;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 5px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .modal-body {
            color: #34495e;
            line-height: 1.8;
            font-size: 1.1em;
            text-align: justify;
            margin-bottom: 20px;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
        }
        
        .modal-footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            color: #7f8c8d;
            font-style: italic;
            background-color: rgba(76, 175, 80, 0.1);
            padding: 12px;
            border-radius: 8px;
            border-left: 3px solid #4CAF50;
        }
        
        .discussion p {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .discussion p:hover {
            background-color: rgba(76, 175, 80, 0.05);
        }
        
        .discussion small {
            color: #7f8c8d;
            font-size: 0.9em;
            font-style: italic;
            display: block;
            padding: 8px 12px;
            background-color: rgba(76, 175, 80, 0.1);
            border-radius: 8px;
            border-left: 3px solid #4CAF50;
        }
        
        .no-discussions {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 40px 20px;
        }
        
        .error-loading {
            text-align: center;
            color: #e74c3c;
            padding: 40px 20px;
        }
        
        .loading-indicator {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4CAF50;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .discussions-container {
                padding: 10px;
            }
            
            .discussions-section {
                padding: 20px;
            }
            
            .section-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
        <header class="encabezado">
        <div class="encabezado-container">
            <div class="header-top">
                <a href="{{ route("index") }}" class="logo-container">
                    <img src="{{ asset('Imagenes/nukidslofgo-Photoroom (1).png') }}" alt="NutriKids Logo" class="logo">
                </a>
                <nav class="menu">
                    <ul>
                        <li><a href="{{ route("index") }}">Inicio</a></li>
                        <li><a href="{{ route("obesidad") }}">¿Obesidad?</a></li>
                        <li><a href="{{ route("calculadora") }}">Cálculo de IMC</a></li>
                        <li><a href="{{ route("nutriologos") }}">Atención Profesional</a></li>
                        <li><a href="{{ route("comentarios") }}">Comentarios</a></li>
                        <li><a href="{{ route("foros") }}" class="active">Discusiones</a></li>
                        <li><a href="{{ route("conocenos") }}">¿Quiénes Somos?</a></li>
                        <li><a href="{{ route("login") }}">Login</a></li>
                    </ul>
                </nav>
            </div>
            <div class="iconos-redes">
                <a href="#" aria-label="Facebook"><img src="{{ asset('Imagenes/2023_Facebook_icon.svg.png') }}" alt="Facebook"></a>
                <a href="#" aria-label="Instagram"><img src="{{ asset('Imagenes/instagramlogo.png') }}" alt="Instagram"></a>
                <a href="#" aria-label="Twitter"><img src="{{ asset('Imagenes/Logo_of_Twitter.svg.png') }}" alt="Twitter"></a>
            </div>
        </div>
    </header>

    <div class="container">
        <header class="forum-header">
            <h1><span class="highlight">Discusiones</span> NutriKids</h1>
            <p>Comunidad de nutrición infantil</p>
        </header>

        <div class="discussions-container">
            <!-- Sección de Discusiones -->
            <section class="discussions-section">
                <div class="section-header">
                    <h2>Discusiones</h2>
                    <div class="header-buttons">
                        <button class="btn-secondary" id="toggle-discussion-form">Nueva Discusión</button>
                        <button class="btn-refresh" id="refresh-discussions" title="Actualizar discusiones">
                            <span>🔄</span>
                        </button>
                    </div>
                </div>
                
                <div class="form-container hidden" id="discussion-form">
                    @auth
                        @if(Auth::user()->rol === 'padre')
                            <form id="discussionForm" action="{{ route("discusiones.store") }}" method="post">
                                @csrf
                                <input type="text" class="form-input" name="tema" placeholder="Tema de la discusión" required maxlength="255" minlength="5" title="El tema debe tener entre 5 y 255 caracteres">
                                <textarea class="form-textarea" name="descripcion" placeholder="Descripción de la discusión..." required maxlength="1000" minlength="10" title="La descripción debe tener entre 10 y 1000 caracteres"></textarea>
                                <button type="submit" class="btn-publish">Publicar</button>
                                <div id="mensajeExitoDiscusion" style="display:none; color: #4CAF50; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #d4edda; border-radius: 5px;"></div>
                                <div id="mensajeErrorDiscusion" style="display:none; color: #721c24; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #f8d7da; border-radius: 5px;"></div>
                                <div id="mensajeCargandoDiscusion" style="display:none; color: #856404; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #fff3cd; border-radius: 5px;">Publicando discusión...</div>
                            </form>
                        @else
                            <p style="color: #666; padding: 20px; background-color: #f0f0f0; border-radius: 5px;">Solo los usuarios con rol de padre pueden crear discusiones.</p>
                        @endif
                    @else
                        <p style="color: #666; padding: 20px; background-color: #f0f0f0; border-radius: 5px;">Debes <a href="{{ route('login') }}" style="color: #4CAF50; text-decoration: underline;">iniciar sesión</a> como padre para crear discusiones.</p>
                    @endauth
                </div>

                <div class="discussions-list" id="discussions-list">
                    <!-- Las discusiones se cargarán dinámicamente aquí -->
                </div>
            </section>
        </div>
    </div>

    <!-- Modal para ver discusión completa -->
    <div class="modal-overlay" id="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-title">Discusión</h3>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- El contenido de la discusión se insertará aquí -->
            </div>
            <div class="modal-footer" id="modal-footer">
                <!-- La fecha se insertará aquí -->
            </div>
        </div>
    </div>

    <script>
// Función para cargar discusiones
function cargarDiscusiones() {
    fetch('{{ route("discusiones.index") }}')
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('discussions-list');
        
        if (data.success && data.discusiones.length > 0) {
            container.innerHTML = '';
            data.discusiones.forEach((discusion, index) => {
                const fecha = new Date(discusion.fecha_creacion);
                const fechaFormateada = fecha.toLocaleDateString('es-ES', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                const usuarioActual = data.usuario_actual;
                const esPropia = discusion.id_usuario && usuarioActual && discusion.id_usuario == usuarioActual;
                const botonesAccion = esPropia ? `
                    <div style="margin-top: 10px; display: flex; gap: 10px;">
                        <button class="btn-editar" onclick="editarDiscusion(${discusion.id_discusion}, ${index})" style="background-color: #2196F3; color: white; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;">Editar</button>
                        <button class="btn-eliminar" onclick="eliminarDiscusion(${discusion.id_discusion})" style="background-color: #f44336; color: white; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;">Eliminar</button>
                    </div>
                ` : '';
                
                const discussionHTML = `
                    <div class="discussion" id="discusion-${discusion.id_discusion}">
                        <h3>${discusion.tema}</h3>
                        <p id="discusion-descripcion-${index}" onclick="abrirModalDiscusion(${index})" title="Haz clic para ver la discusión completa">${discusion.descripcion}</p>
                        <button class="btn-leer-mas-discusion hidden" id="btn-leer-mas-discusion-${index}" onclick="toggleDiscusion(${index})">Leer más</button>
                        <small>Creado el: ${fechaFormateada}</small>
                        ${botonesAccion}
                    </div>
                `;
                container.innerHTML += discussionHTML;
            });
            
            // Verificar si las discusiones necesitan el botón "leer más"
            setTimeout(() => {
                verificarDiscusionesLargas();
            }, 100);
            
            // Guardar las discusiones para usar en el modal
            window.discusionesData = data.discusiones;
        } else {
            container.innerHTML = '<p class="no-discussions">No hay discusiones disponibles. ¡Sé el primero en crear una!</p>';
        }
    })
    .catch(error => {
        console.error('Error al cargar discusiones:', error);
        const container = document.getElementById('discussions-list');
        container.innerHTML = '<p class="error-loading">Error al cargar las discusiones. Por favor, recarga la página.</p>';
    });
}

// Función para verificar si las discusiones son largas y necesitan el botón "leer más"
function verificarDiscusionesLargas() {
    const discusiones = document.querySelectorAll('.discussion p');
    discusiones.forEach((discusion, index) => {
        const btnLeerMas = document.getElementById(`btn-leer-mas-discusion-${index}`);
        if (btnLeerMas) {
            // Verificar si el contenido es más alto que el contenedor
            const scrollHeight = discusion.scrollHeight;
            const clientHeight = discusion.clientHeight;
            
            if (scrollHeight > clientHeight) {
                btnLeerMas.classList.remove('hidden');
            } else {
                btnLeerMas.classList.add('hidden');
            }
        }
    });
}

// Función para expandir/contraer discusiones
function toggleDiscusion(index) {
    const discusion = document.getElementById(`discusion-descripcion-${index}`);
    const btnLeerMas = document.getElementById(`btn-leer-mas-discusion-${index}`);
    
    if (discusion.classList.contains('expanded')) {
        // Contraer
        discusion.classList.remove('expanded');
        btnLeerMas.textContent = 'Leer más';
    } else {
        // Expandir
        discusion.classList.add('expanded');
        btnLeerMas.textContent = 'Leer menos';
    }
}

// Función para abrir modal con discusión completa
function abrirModalDiscusion(index) {
    if (window.discusionesData && window.discusionesData[index]) {
        const discusion = window.discusionesData[index];
        const fecha = new Date(discusion.fecha_creacion);
        const fechaFormateada = fecha.toLocaleDateString('es-ES', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        document.getElementById('modal-title').textContent = discusion.tema;
        document.getElementById('modal-body').textContent = discusion.descripcion;
        document.getElementById('modal-footer').textContent = `Creado el: ${fechaFormateada}`;
        
        document.getElementById('modal-overlay').style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevenir scroll del body
    }
}

// Función para cerrar modal
function cerrarModal() {
    document.getElementById('modal-overlay').style.display = 'none';
    document.body.style.overflow = 'auto'; // Restaurar scroll del body
}

// Cerrar modal al hacer clic fuera del contenido
document.addEventListener('DOMContentLoaded', function() {
    const modalOverlay = document.getElementById('modal-overlay');
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            cerrarModal();
        }
    });
    
    // Cerrar modal con la tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay.style.display === 'flex') {
            cerrarModal();
        }
    });
});

// Cargar discusiones al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarDiscusiones();
});

// Event listener para el botón de recarga
const refreshButton = document.getElementById('refresh-discussions');
if (refreshButton) {
    refreshButton.addEventListener('click', function() {
        this.style.transform = 'rotate(360deg)';
        cargarDiscusiones();
        setTimeout(() => {
            this.style.transform = 'rotate(0deg)';
        }, 1000);
    });
}

// Función para mostrar errores de validación
function mostrarError(campo, mensaje) {
    const input = document.querySelector(`[name="${campo}"]`);
    input.style.borderColor = '#d32f2f';
    input.style.backgroundColor = '#ffebee';
    
    // Crear o actualizar mensaje de error
    let errorDiv = document.getElementById(`error-${campo}`);
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = `error-${campo}`;
        errorDiv.style.color = '#d32f2f';
        errorDiv.style.fontSize = '12px';
        errorDiv.style.marginTop = '5px';
        input.parentNode.insertBefore(errorDiv, input.nextSibling);
    }
    errorDiv.textContent = mensaje;
    errorDiv.style.display = 'block';
}

// Función para limpiar errores
function limpiarError(campo) {
    const input = document.querySelector(`[name="${campo}"]`);
    input.style.borderColor = '';
    input.style.backgroundColor = '';
    
    const errorDiv = document.getElementById(`error-${campo}`);
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

// Validaciones en tiempo real para discusiones
document.querySelector('input[name="tema"]').addEventListener('input', function() {
    const valor = this.value.trim();
    if (valor.length === 0) {
        mostrarError('tema', 'El tema es requerido');
    } else if (valor.length < 5) {
        mostrarError('tema', 'El tema debe tener al menos 5 caracteres');
    } else if (valor.length > 100) {
        mostrarError('tema', 'El tema no puede exceder 100 caracteres');
    } else {
        limpiarError('tema');
    }
});

document.querySelector('textarea[name="descripcion"]').addEventListener('input', function() {
    const valor = this.value.trim();
    if (valor.length === 0) {
        mostrarError('descripcion', 'La descripción es requerida');
    } else if (valor.length < 20) {
        mostrarError('descripcion', 'La descripción debe tener al menos 20 caracteres');
    } else if (valor.length > 1000) {
        mostrarError('descripcion', 'La descripción no puede exceder 1000 caracteres');
    } else {
        limpiarError('descripcion');
    }
});

// Función para validar formulario de discusiones
function validarFormularioDiscusion() {
    const tema = document.querySelector('input[name="tema"]').value.trim();
    const descripcion = document.querySelector('textarea[name="descripcion"]').value.trim();
    
    let esValido = true;
    
    if (tema.length === 0) {
        mostrarError('tema', 'El tema es requerido');
        esValido = false;
    } else if (tema.length < 5) {
        mostrarError('tema', 'El tema debe tener al menos 5 caracteres');
        esValido = false;
    } else if (tema.length > 100) {
        mostrarError('tema', 'El tema no puede exceder 100 caracteres');
        esValido = false;
    }
    
    if (descripcion.length === 0) {
        mostrarError('descripcion', 'La descripción es requerida');
        esValido = false;
    } else if (descripcion.length < 20) {
        mostrarError('descripcion', 'La descripción debe tener al menos 20 caracteres');
        esValido = false;
    } else if (descripcion.length > 1000) {
        mostrarError('descripcion', 'La descripción no puede exceder 1000 caracteres');
        esValido = false;
    }
    
    return esValido;
}

// Enviar el formulario de discusión por AJAX
const discussionForm = document.getElementById('discussionForm');
const mensajeExitoDiscusion = document.getElementById('mensajeExitoDiscusion');
const mensajeErrorDiscusion = document.getElementById('mensajeErrorDiscusion');
const mensajeCargandoDiscusion = document.getElementById('mensajeCargandoDiscusion');

if (discussionForm) {
    discussionForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar formulario antes de enviar
        if (!validarFormularioDiscusion()) {
            return;
        }
        
        // Ocultar mensajes anteriores
        mensajeExitoDiscusion.style.display = 'none';
        mensajeErrorDiscusion.style.display = 'none';
        mensajeCargandoDiscusion.style.display = 'block';
        
        const formData = new FormData(discussionForm);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
        
        fetch('{{ route("discusiones.store") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            mensajeCargandoDiscusion.style.display = 'none';
            
            if (data.success) {
                mensajeExitoDiscusion.textContent = data.message;
                mensajeExitoDiscusion.style.display = 'block';
                discussionForm.reset();
                
                // Limpiar errores
                ['tema', 'descripcion'].forEach(campo => {
                    limpiarError(campo);
                });
                
                // Recargar discusiones
                cargarDiscusiones();
                
                // Ocultar mensaje de éxito después de 5 segundos
                setTimeout(() => {
                    mensajeExitoDiscusion.style.display = 'none';
                }, 5000);
            } else {
                mensajeErrorDiscusion.textContent = data.message;
                mensajeErrorDiscusion.style.display = 'block';
                
                // Ocultar mensaje de error después de 8 segundos
                setTimeout(() => {
                    mensajeErrorDiscusion.style.display = 'none';
                }, 8000);
            }
        })
        .catch(error => {
            mensajeCargandoDiscusion.style.display = 'none';
            console.error('Error:', error);
            mensajeErrorDiscusion.textContent = 'Error de conexión. Por favor, inténtalo de nuevo.';
            mensajeErrorDiscusion.style.display = 'block';
            
            // Ocultar mensaje de error después de 8 segundos
            setTimeout(() => {
                mensajeErrorDiscusion.style.display = 'none';
            }, 8000);
        });
    });
}

// Mostrar/ocultar formulario de discusión
const toggleDiscussionBtn = document.getElementById('toggle-discussion-form');
const discussionFormContainer = document.getElementById('discussion-form');
if (toggleDiscussionBtn && discussionFormContainer) {
    toggleDiscussionBtn.addEventListener('click', function() {
        discussionFormContainer.classList.toggle('hidden');
    });
}
    </script>
    
    <footer class="footer">
        <div class="footer-container">
            <div class="contacto">
                <h3>Contáctanos</h3>
                <form id="contactoForm" action="{{ route("contacto.store") }}" method="post">
                    @csrf
                    <input type="text" name="nombre" placeholder="Nombre" required maxlength="50" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+" title="Solo letras y espacios permitidos">
                    <input type="text" name="apellido" placeholder="Apellido" required maxlength="50" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+" title="Solo letras y espacios permitidos">
                    <input type="email" name="email" placeholder="Email" required maxlength="100" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Ingrese un email válido (ejemplo: usuario@dominio.com)">
                    <textarea name="mensaje" placeholder="Mensaje" required maxlength="500" minlength="10" title="El mensaje debe tener entre 10 y 500 caracteres"></textarea>
                    <button type="submit" id="btnEnviar">Enviar</button>
                    <div id="mensajeExito" style="display:none; color: #4CAF50; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #d4edda; border-radius: 5px;"></div>
                    <div id="mensajeError" style="display:none; color: #721c24; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #f8d7da; border-radius: 5px;"></div>
                    <div id="mensajeCargando" style="display:none; color: #856404; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #fff3cd; border-radius: 5px;">Enviando mensaje...</div>
                </form>
            </div>
    
            <div class="info">
                <h3>Teléfono</h3>
                <p>55 3908 5006 - Soporte Técnico</p>
                <p>442 776 3385 - Soporte Técnico</p>
                <p>442 545 1626 - Contacto Nutrióloga</p>
    
                <h3>Dirección</h3>
                <p>Carretera Estatal 420 S/N, El Rosario</p>
                <p>Santiago de Querétaro, Qro</p>
            </div>
    
            <div class="redes">
                <h3>Síguenos</h3>
                <a href="#"><img src="{{ asset('Imagenes/2023_Facebook_icon.svg.png') }}" alt="Facebook"></a>
                <a href="#"><img src="{{ asset('Imagenes/instagramlogo.png') }}" alt="Instagram"></a>
                <a href="#"><img src="{{ asset('Imagenes/Logo_of_Twitter.svg.png') }}" alt="Twitter"></a>
            </div>
        </div>
    </footer>
    
    <script>
// Función para validar email
function validarEmail(email) {
    const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return regex.test(email);
}

// Función para validar solo letras y espacios
function validarSoloLetras(texto) {
    const regex = /^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/;
    return regex.test(texto);
}

// Función para mostrar errores de validación
function mostrarError(campo, mensaje) {
    const input = document.querySelector(`[name="${campo}"]`);
    input.style.borderColor = '#d32f2f';
    input.style.backgroundColor = '#ffebee';
    
    // Crear o actualizar mensaje de error
    let errorDiv = document.getElementById(`error-${campo}`);
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = `error-${campo}`;
        errorDiv.style.color = '#d32f2f';
        errorDiv.style.fontSize = '12px';
        errorDiv.style.marginTop = '5px';
        input.parentNode.insertBefore(errorDiv, input.nextSibling);
    }
    errorDiv.textContent = mensaje;
    errorDiv.style.display = 'block';
}

// Función para limpiar errores
function limpiarError(campo) {
    const input = document.querySelector(`[name="${campo}"]`);
    input.style.borderColor = '';
    input.style.backgroundColor = '';
    
    const errorDiv = document.getElementById(`error-${campo}`);
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

// Validaciones en tiempo real
document.querySelector('input[name="nombre"]').addEventListener('input', function() {
    const valor = this.value.trim();
    if (valor.length === 0) {
        mostrarError('nombre', 'El nombre es requerido');
    } else if (!validarSoloLetras(valor)) {
        mostrarError('nombre', 'Solo se permiten letras y espacios');
    } else if (valor.length < 2) {
        mostrarError('nombre', 'El nombre debe tener al menos 2 caracteres');
    } else {
        limpiarError('nombre');
    }
});

document.querySelector('input[name="apellido"]').addEventListener('input', function() {
    const valor = this.value.trim();
    if (valor.length === 0) {
        mostrarError('apellido', 'El apellido es requerido');
    } else if (!validarSoloLetras(valor)) {
        mostrarError('apellido', 'Solo se permiten letras y espacios');
    } else if (valor.length < 2) {
        mostrarError('apellido', 'El apellido debe tener al menos 2 caracteres');
    } else {
        limpiarError('apellido');
    }
});

document.querySelector('input[name="email"]').addEventListener('input', function() {
    const valor = this.value.trim();
    if (valor.length === 0) {
        mostrarError('email', 'El email es requerido');
    } else if (!validarEmail(valor)) {
        mostrarError('email', 'Ingrese un email válido (ejemplo: usuario@dominio.com)');
    } else {
        limpiarError('email');
    }
});

document.querySelector('textarea[name="mensaje"]').addEventListener('input', function() {
    const valor = this.value.trim();
    if (valor.length === 0) {
        mostrarError('mensaje', 'El mensaje es requerido');
    } else if (valor.length < 10) {
        mostrarError('mensaje', 'El mensaje debe tener al menos 10 caracteres');
    } else if (valor.length > 500) {
        mostrarError('mensaje', 'El mensaje no puede exceder 500 caracteres');
    } else {
        limpiarError('mensaje');
    }
});

// Función para validar todo el formulario
function validarFormulario() {
    const nombre = document.querySelector('input[name="nombre"]').value.trim();
    const apellido = document.querySelector('input[name="apellido"]').value.trim();
    const email = document.querySelector('input[name="email"]').value.trim();
    const mensaje = document.querySelector('textarea[name="mensaje"]').value.trim();
    
    let esValido = true;
    
    // Validar nombre
    if (nombre.length === 0) {
        mostrarError('nombre', 'El nombre es requerido');
        esValido = false;
    } else if (!validarSoloLetras(nombre)) {
        mostrarError('nombre', 'Solo se permiten letras y espacios');
        esValido = false;
    } else if (nombre.length < 2) {
        mostrarError('nombre', 'El nombre debe tener al menos 2 caracteres');
        esValido = false;
    }
    
    // Validar apellido
    if (apellido.length === 0) {
        mostrarError('apellido', 'El apellido es requerido');
        esValido = false;
    } else if (!validarSoloLetras(apellido)) {
        mostrarError('apellido', 'Solo se permiten letras y espacios');
        esValido = false;
    } else if (apellido.length < 2) {
        mostrarError('apellido', 'El apellido debe tener al menos 2 caracteres');
        esValido = false;
    }
    
    // Validar email
    if (email.length === 0) {
        mostrarError('email', 'El email es requerido');
        esValido = false;
    } else if (!validarEmail(email)) {
        mostrarError('email', 'Ingrese un email válido (ejemplo: usuario@dominio.com)');
        esValido = false;
    }
    
    // Validar mensaje
    if (mensaje.length === 0) {
        mostrarError('mensaje', 'El mensaje es requerido');
        esValido = false;
    } else if (mensaje.length < 10) {
        mostrarError('mensaje', 'El mensaje debe tener al menos 10 caracteres');
        esValido = false;
    } else if (mensaje.length > 500) {
        mostrarError('mensaje', 'El mensaje no puede exceder 500 caracteres');
        esValido = false;
    }
    
    return esValido;
}

// Script para manejar el envío del formulario de contacto por AJAX
const form = document.getElementById('contactoForm');
const mensajeExito = document.getElementById('mensajeExito');
const mensajeError = document.getElementById('mensajeError');
const mensajeCargando = document.getElementById('mensajeCargando');
const btnEnviar = document.getElementById('btnEnviar');

form.addEventListener('submit', function(event) {
    event.preventDefault(); // Prevenir envío normal del formulario
    
    // Validar formulario antes de enviar
    if (!validarFormulario()) {
        return;
    }
    
    // Ocultar mensajes anteriores
    mensajeExito.style.display = 'none';
    mensajeError.style.display = 'none';
    mensajeCargando.style.display = 'block';
    btnEnviar.disabled = true;
    
    // Obtener datos del formulario
    const formData = new FormData(form);
    
    // Obtener token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
    
    // Enviar datos por AJAX
    fetch('{{ route("contacto.store") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                return response.json().then(err => { throw err; });
            } else {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
        }
        return response.json();
    })
    .then(data => {
        mensajeCargando.style.display = 'none';
        btnEnviar.disabled = false;
        
        if (data.success) {
            // Éxito - Ocultar mensaje de error si existe
            mensajeError.style.display = 'none';
            mensajeExito.textContent = data.message;
            mensajeExito.style.display = 'block';
            form.reset(); // Limpiar formulario
            
            // Limpiar todos los errores
            ['nombre', 'apellido', 'email', 'mensaje'].forEach(campo => {
                limpiarError(campo);
            });
            
            // Ocultar mensaje de éxito después de 5 segundos
            setTimeout(() => {
                mensajeExito.style.display = 'none';
            }, 5000);
        } else {
            // Error - Ocultar mensaje de éxito si existe
            mensajeExito.style.display = 'none';
            let errorMsg = data.message;
            if (data.errors && data.errors.length > 0) {
                errorMsg += ': ' + data.errors.join(', ');
            }
            mensajeError.textContent = errorMsg;
            mensajeError.style.display = 'block';
            
            // Ocultar mensaje de error después de 8 segundos
            setTimeout(() => {
                mensajeError.style.display = 'none';
            }, 8000);
        }
    })
    .catch(error => {
        mensajeCargando.style.display = 'none';
        btnEnviar.disabled = false;
        
        // Ocultar mensaje de éxito si existe
        mensajeExito.style.display = 'none';
        
        console.error('Error:', error);
        let displayError = 'Error de conexión. Por favor, inténtalo de nuevo.';
        
        // Si el error es un objeto con success, usar su mensaje
        if (error && typeof error === 'object') {
            if (error.success === false && error.message) {
                displayError = error.message;
            } else if (error.message) {
                if (!error.message.includes('CSRF token mismatch')) {
                    displayError = error.message;
                }
            }
        } else if (typeof error === 'string') {
            displayError = error;
        }
        
        // No mostrar errores técnicos al usuario
        if (displayError.includes('SQLSTATE') || displayError.includes('Unknown column') || displayError.includes('Connection')) {
            displayError = 'Error al enviar el mensaje. Por favor, inténtalo de nuevo.';
        }
        
        mensajeError.textContent = displayError;
        mensajeError.style.display = 'block';
        
        // Ocultar mensaje de error después de 8 segundos
        setTimeout(() => {
            mensajeError.style.display = 'none';
        }, 8000);
    });
});
</script>
    </div>
</body>
</html>
</html>