<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - NutriKids</title>
    <link rel="stylesheet" href="{{ asset('CSS/style.css') }}">
    <script src="{{ asset('js/session.js') }}"></script>
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

        /* Estilos para el dashboard */
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome-section {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .welcome-section h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
        }

        .welcome-section p {
            margin: 0;
            font-size: 1.2em;
            opacity: 0.9;
        }

        .user-info {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            display: inline-block;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e0e0e0;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .dashboard-card h3 {
            color: #4CAF50;
            margin-bottom: 15px;
            font-size: 1.4em;
        }

        .dashboard-card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .dashboard-card .btn {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s ease;
            font-weight: bold;
        }

        .dashboard-card .btn:hover {
            background: #45a049;
        }

        .logout-section {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #4CAF50;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-section {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .welcome-section h1 {
                font-size: 2em;
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

    <div class="dashboard-container">
        <!-- Sección de bienvenida -->
        <div class="welcome-section">
            <h1>¡Bienvenido a NutriKids!</h1>
            <p>Tu plataforma para una nutrición saludable en la infancia</p>
            <div class="user-info" id="userInfo">
                <!-- La información del usuario se cargará dinámicamente -->
            </div>
        </div>

        <!-- Sección de estadísticas -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Cálculos de IMC</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Comentarios</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Discusiones</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Días activo</div>
            </div>
        </div>

        <!-- Grid de funcionalidades -->
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>📊 Calculadora de IMC</h3>
                <p>Calcula el índice de masa corporal de tu hijo y obtén recomendaciones personalizadas para su edad y desarrollo.</p>
                <a href="{{ route("calculadora") }}" class="btn">Ir a Calculadora</a>
            </div>

            <div class="dashboard-card">
                <h3>👨‍⚕️ Atención Profesional</h3>
                <p>Conoce a nuestra especialista en nutrición infantil y agenda una consulta personalizada para tu familia.</p>
                <a href="{{ route("nutriologos") }}" class="btn">Ver Nutrióloga</a>
            </div>

            <div class="dashboard-card">
                <h3>💬 Comentarios</h3>
                <p>Comparte tu experiencia y lee las opiniones de otros padres sobre nutrición infantil y hábitos saludables.</p>
                <a href="{{ route("comentarios") }}" class="btn">Ver Comentarios</a>
            </div>

            <div class="dashboard-card">
                <h3>🗨️ Discusiones</h3>
                <p>Participa en foros de discusión sobre temas específicos de nutrición infantil y crianza saludable.</p>
                <a href="{{ route("foros") }}" class="btn">Ver Discusiones</a>
            </div>

            <div class="dashboard-card">
                <h3>📚 Información sobre Obesidad</h3>
                <p>Infórmate sobre los riesgos de la obesidad infantil y cómo prevenirla desde temprana edad.</p>
                <a href="{{ route("obesidad") }}" class="btn">Leer Más</a>
            </div>

            <div class="dashboard-card">
                <h3>👥 ¿Quiénes Somos?</h3>
                <p>Conoce más sobre el proyecto NutriKids y nuestro compromiso con la salud nutricional infantil.</p>
                <a href="{{ route("conocenos") }}" class="btn">Conocer Más</a>
            </div>
        </div>

        <!-- Sección de cerrar sesión -->
        <div class="logout-section">
            <button onclick="cerrarSesion()" class="logout-btn">Cerrar Sesión</button>
        </div>
    </div>

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
        // Función para cargar información del usuario desde localStorage
        function cargarInformacionUsuario() {
            const usuario = window.sessionManager.verificarSesion();
            if (usuario) {
                const userInfoElement = document.getElementById('userInfo');
                userInfoElement.innerHTML = `
                    <strong>Usuario:</strong> ${usuario.nombre} ${usuario.apellido_paterno}<br>
                    <strong>Email:</strong> ${usuario.email}
                `;
            }
        }

        // Función para cerrar sesión
        function cerrarSesion() {
            window.sessionManager.cerrarSesion();
        }

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

        // Función para manejar el envío del formulario de contacto por AJAX
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

        // Cargar información del usuario al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            cargarInformacionUsuario();
        });
    </script>
</body>
</html> 