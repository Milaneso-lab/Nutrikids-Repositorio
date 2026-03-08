<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('CSS/style.css') }}">
    <title>NutriKids - Comentarios</title>
    <style>
        /* Estilos para el formulario/* Estilos para el encabezado y menú */
        .encabezado-container {
            display: flex;
            flex-direction: column;
            align-items: center;
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
        }
        
        .menu li {
            list-style: none;
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
        .menu li a[href="{{ route("comentarios") }}"] {
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
        
        /* Estilos para la sección de comentarios */
        .comentarios-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin: 30px auto;
            max-width: 1200px;
        }
        
        .formulario-comentario {
            flex: 1;
            min-width: 300px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .lista-comentarios {
            flex: 1;
            min-width: 300px;
        }
        
        .comentario {
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .comentario-info h4 {
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .comentario-info h4 span {
            font-weight: normal;
            color: #666;
            font-size: 0.9em;
        }
        
        .comentario-info p {
            margin: 0;
            color: #444;
            line-height: 1.5;
            max-height: 4.5em; /* Limita a 3 líneas aproximadamente */
            overflow: hidden;
            position: relative;
            transition: max-height 0.3s ease;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .comentario-info p.expanded {
            max-height: none;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .comentario-info p::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 50px;
            height: 1.5em;
            background: linear-gradient(to right, transparent, white);
            pointer-events: none;
            opacity: 1;
            transition: opacity 0.3s ease;
        }
        
        .comentario-info p.expanded::after {
            opacity: 0;
        }
        
        .btn-leer-mas {
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
        
        .btn-leer-mas:hover {
            color: #45a049;
        }
        
        .btn-leer-mas.hidden {
            display: none;
        }
        
        /* Modal para ver comentario completo */
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
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
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
            font-size: 1.4em;
            font-weight: bold;
            margin: 0;
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
            line-height: 1.7;
            font-size: 1.1em;
            text-align: justify;
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
        }
        
        .comentario-info p {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .comentario-info p:hover {
            background-color: rgba(76, 175, 80, 0.05);
        }
        
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        button:hover {
            background-color: #45a049;
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
                        <li><a href="{{ route("comentarios") }}" class="active">Comentarios</a></li>
                        <li><a href="{{ route("foros") }}">Discusiones</a></li>
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

    <section class="comentarios-container">
        <!-- Parte izquierda: Formulario para escribir comentarios -->
        <div class="formulario-comentario">
            <h2>Escribe tu comentario</h2>
            @auth
                @if(Auth::user()->rol === 'padre')
                    <form id="form-comentario" action="{{ route("comentarios.store") }}" method="post">
                        @csrf
                        <textarea id="comentario" name="comentario" required placeholder="Escribe tu comentario aquí..." maxlength="1000" minlength="5" title="El comentario debe tener entre 5 y 1000 caracteres"></textarea>
                        <button type="submit" id="btnComentar">Comentar</button>
                        <div id="mensajeExitoComentario" style="display:none; color: #4CAF50; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #d4edda; border-radius: 5px;"></div>
                        <div id="mensajeErrorComentario" style="display:none; color: #721c24; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #f8d7da; border-radius: 5px;"></div>
                        <div id="mensajeCargandoComentario" style="display:none; color: #856404; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #fff3cd; border-radius: 5px;">Enviando comentario...</div>
                    </form>
                @else
                    <p style="color: #666; padding: 20px; background-color: #f0f0f0; border-radius: 5px;">Solo los usuarios con rol de padre pueden publicar comentarios.</p>
                @endif
            @else
                <p style="color: #666; padding: 20px; background-color: #f0f0f0; border-radius: 5px;">Debes <a href="{{ route('login') }}" style="color: #4CAF50; text-decoration: underline;">iniciar sesión</a> como padre para publicar comentarios.</p>
            @endauth
        </div>
        
        <!-- Parte derecha: Lista de comentarios publicados -->
        <div class="lista-comentarios">
            <h2>Comentarios Recientes</h2>
            <div id="comentarios-container">
                <!-- Los comentarios se cargarán dinámicamente desde la base de datos -->
            </div>
        </div>
    </section>

    <!-- Modal para ver comentario completo -->
    <div class="modal-overlay" id="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-title">Comentario</h3>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- El contenido del comentario se insertará aquí -->
            </div>
            <div class="modal-footer" id="modal-footer">
                <!-- La fecha se insertará aquí -->
            </div>
        </div>
    </div>

    <!-- Footer -->
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

// Función para cargar comentarios desde la base de datos
function cargarComentarios() {
    fetch('{{ route("comentarios.index") }}')
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('comentarios-container');
        
        if (data.success && data.comentarios.length > 0) {
            container.innerHTML = '';
            data.comentarios.forEach((comentario, index) => {
                const fecha = new Date(comentario.fecha_comentario);
                const fechaFormateada = fecha.toLocaleDateString('es-ES', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
                
                const comentarioHTML = `
                    <div class="comentario">
                        <div class="comentario-info">
                            <h4>${comentario.nombre} ${comentario.apellido} <span>• ${fechaFormateada}</span></h4>
                            <p id="comentario-texto-${index}" onclick="abrirModalComentario(${index})" title="Haz clic para ver el comentario completo">${comentario.comentario}</p>
                            <button class="btn-leer-mas hidden" id="btn-leer-mas-${index}" onclick="toggleComentario(${index})">Leer más</button>
                        </div>
                    </div>
                `;
                container.innerHTML += comentarioHTML;
            });
            
            // Verificar si los comentarios necesitan el botón "leer más"
            setTimeout(() => {
                verificarComentariosLargos();
            }, 100);
            
            // Guardar los comentarios para usar en el modal
            window.comentariosData = data.comentarios;
        } else {
            container.innerHTML = '<p style="text-align: center; color: #666; font-style: italic;">No hay comentarios aún. ¡Sé el primero en comentar!</p>';
        }
    })
    .catch(error => {
        console.error('Error al cargar comentarios:', error);
        const container = document.getElementById('comentarios-container');
        container.innerHTML = '<p style="text-align: center; color: #721c24;">Error al cargar comentarios. Por favor, recarga la página.</p>';
    });
}

// Función para verificar si los comentarios son largos y necesitan el botón "leer más"
function verificarComentariosLargos() {
    const comentarios = document.querySelectorAll('.comentario-info p');
    comentarios.forEach((comentario, index) => {
        const btnLeerMas = document.getElementById(`btn-leer-mas-${index}`);
        if (btnLeerMas) {
            // Verificar si el contenido es más alto que el contenedor
            const scrollHeight = comentario.scrollHeight;
            const clientHeight = comentario.clientHeight;
            
            if (scrollHeight > clientHeight) {
                btnLeerMas.classList.remove('hidden');
            } else {
                btnLeerMas.classList.add('hidden');
            }
        }
    });
}

// Función para expandir/contraer comentarios
function toggleComentario(index) {
    const comentario = document.getElementById(`comentario-texto-${index}`);
    const btnLeerMas = document.getElementById(`btn-leer-mas-${index}`);
    
    if (comentario.classList.contains('expanded')) {
        // Contraer
        comentario.classList.remove('expanded');
        btnLeerMas.textContent = 'Leer más';
    } else {
        // Expandir
        comentario.classList.add('expanded');
        btnLeerMas.textContent = 'Leer menos';
    }
}

// Función para abrir modal con comentario completo
function abrirModalComentario(index) {
    if (window.comentariosData && window.comentariosData[index]) {
        const comentario = window.comentariosData[index];
        const fecha = new Date(comentario.fecha_comentario);
        const fechaFormateada = fecha.toLocaleDateString('es-ES', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        document.getElementById('modal-title').textContent = `Comentario de ${comentario.nombre} ${comentario.apellido}`;
        document.getElementById('modal-body').textContent = comentario.comentario;
        document.getElementById('modal-footer').textContent = `Publicado el: ${fechaFormateada}`;
        
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

// Cargar comentarios al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarComentarios();
});

// Script para manejar el envío del formulario de contacto por AJAX
const form = document.getElementById('contactoForm');
const mensajeExito = document.getElementById('mensajeExito');
const mensajeError = document.getElementById('mensajeError');
const mensajeCargando = document.getElementById('mensajeCargando');
const btnEnviar = document.getElementById('btnEnviar');

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

// Cargar comentarios al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarComentarios();
});
</script>
</body>
</html>