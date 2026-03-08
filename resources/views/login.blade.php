<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - NutriKids</title>
    <link rel="stylesheet" href="{{ asset('CSS/style.css') }}">
    <script src="{{ asset('js/session.js') }}"></script>
    <style>
        /* Estilos para el formulario */
        .auth-container {
            display: flex;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .auth-box {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin: 20px;
        }
        
        .auth-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .auth-tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
            color: #666;
        }
        
        .auth-tab.active {
            color: #4CAF50;
            border-bottom: 2px solid #4CAF50;
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-group input:focus {
            border-color: #4CAF50;
            outline: none;
        }
        
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button[type="submit"]:hover {
            background-color: #45a049;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .auth-footer a {
            color: #4CAF50;
            text-decoration: none;
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
                        <li><a href="{{ route("login") }}" class="active">Login</a></li>
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

    <div class="auth-container">
        <div class="auth-box">
            <!-- Sección de usuario logueado (inicialmente oculta) -->
            <div id="userLoggedIn" style="display: none;">
                <div style="text-align: center; padding: 20px;">
                    <h3 style="color: #4CAF50; margin-bottom: 15px;">✅ Sesión Iniciada</h3>
                    <p id="userInfo" style="margin-bottom: 20px; color: #666;"></p>
                    <button onclick="cerrarSesion()" style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Cerrar Sesión</button>
                </div>
            </div>
            
            <!-- Sección de formularios (inicialmente visible) -->
            <div id="authForms">
                <div class="auth-tabs">
                    <div class="auth-tab active" id="loginTab">Iniciar Sesión</div>
                    <div class="auth-tab" id="registerTab">Registrarse</div>
                </div>
            
            <!-- Formulario de Login -->
            <form class="auth-form active" id="loginForm">
                @csrf
                <div class="form-group">
                    <label for="loginEmail">Email</label>
                    <input type="email" id="loginEmail" name="email" required maxlength="100" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Ingrese un email válido" placeholder="nutriologo@nutrikids.com">
                    <small class="error-message" id="error-loginEmail" style="display: none; color: #d32f2f; font-size: 12px;"></small>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Contraseña</label>
                    <div class="password-container">
                        <input type="password" id="loginPassword" name="contrasena" required minlength="8" maxlength="50" title="La contraseña debe tener al menos 8 caracteres" placeholder="nutriologo123">
                        <span class="toggle-password" onclick="togglePassword('loginPassword')">👁️</span>
                    </div>
                    <small class="error-message" id="error-loginPassword" style="display: none; color: #d32f2f; font-size: 12px;"></small>
                </div>
                <button type="submit" id="btnLogin">Iniciar Sesión</button>
                <div id="mensajeExitoLogin" style="display:none; color: #4CAF50; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #d4edda; border-radius: 5px;"></div>
                <div id="mensajeErrorLogin" style="display:none; color: #721c24; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #f8d7da; border-radius: 5px;"></div>
                <div id="mensajeCargandoLogin" style="display:none; color: #856404; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #fff3cd; border-radius: 5px;">Iniciando sesión...</div>
                <div class="auth-footer">
                    <a href="#">¿Olvidaste tu contraseña?</a>
                </div>
            </form>
            
            <!-- Formulario de Registro -->
            <form class="auth-form" id="registerForm">
                <div class="form-group">
                    <label for="registerName">Nombre</label>
                    <input type="text" id="registerName" name="nombre" required maxlength="50" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+" title="Solo letras y espacios permitidos">
                    <small class="error-message" id="error-registerName" style="display: none; color: #d32f2f; font-size: 12px;"></small>
                </div>
                <div class="form-group">
                    <label for="registerApellidoPaterno">Apellido Paterno</label>
                    <input type="text" id="registerApellidoPaterno" name="apellido_paterno" required maxlength="50" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+" title="Solo letras y espacios permitidos">
                    <small class="error-message" id="error-registerApellidoPaterno" style="display: none; color: #d32f2f; font-size: 12px;"></small>
                </div>
                <div class="form-group">
                    <label for="registerApellidoMaterno">Apellido Materno (Opcional)</label>
                    <input type="text" id="registerApellidoMaterno" name="apellido_materno" maxlength="50" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]*" title="Solo letras y espacios permitidos">
                    <small class="error-message" id="error-registerApellidoMaterno" style="display: none; color: #d32f2f; font-size: 12px;"></small>
                </div>
                <div class="form-group">
                    <label for="registerEmail">Email</label>
                    <input type="email" id="registerEmail" name="email" required maxlength="100" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Ingrese un email válido">
                    <small class="error-message" id="error-registerEmail" style="display: none; color: #d32f2f; font-size: 12px;"></small>
                </div>
                <div class="form-group">
                    <label for="registerPassword">Contraseña</label>
                    <div class="password-container">
                        <input type="password" id="registerPassword" name="contrasena" required minlength="8" maxlength="50" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$" title="La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números">
                        <span class="toggle-password" onclick="togglePassword('registerPassword')">👁️</span>
                    </div>
                    <small>La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números.</small>
                    <small class="error-message" id="error-registerPassword" style="display: none; color: #d32f2f; font-size: 12px;"></small>
                </div>
                <button type="submit" id="btnRegistrar">Registrarse</button>
                <div id="mensajeExitoRegistro" style="display:none; color: #4CAF50; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #d4edda; border-radius: 5px;"></div>
                <div id="mensajeErrorRegistro" style="display:none; color: #721c24; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #f8d7da; border-radius: 5px;"></div>
                <div id="mensajeCargandoRegistro" style="display:none; color: #856404; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #fff3cd; border-radius: 5px;">Registrando usuario...</div>
            </form>
            </div>
        </div>
    </div>

    <script>
        // Variables globales para el estado de sesión
        let usuarioLogueado = null;
        
        // Cambiar entre pestañas
        document.getElementById('loginTab').addEventListener('click', function() {
            document.getElementById('loginTab').classList.add('active');
            document.getElementById('registerTab').classList.remove('active');
            document.getElementById('loginForm').classList.add('active');
            document.getElementById('registerForm').classList.remove('active');
        });
        
        document.getElementById('registerTab').addEventListener('click', function() {
            document.getElementById('registerTab').classList.add('active');
            document.getElementById('loginTab').classList.remove('active');
            document.getElementById('registerForm').classList.add('active');
            document.getElementById('loginForm').classList.remove('active');
        });
        
        // Mostrar/ocultar contraseña
        function togglePassword(id) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }
        
        // Función para mostrar estado de sesión
        function mostrarSesionIniciada(usuario, redirectUrl) {
            // Guardar datos del usuario en localStorage
            localStorage.setItem('usuarioLogueado', JSON.stringify(usuario));
            sessionStorage.setItem('usuarioLogueado', JSON.stringify(usuario));
            
            // Redirigir según el rol o URL proporcionada
            if (redirectUrl) {
                window.location.href = redirectUrl;
            } else {
                // Redirección por defecto según rol
                if (usuario.rol === 'admin') {
                    window.location.href = '{{ route("admin.dashboard") }}';
                } else if (usuario.rol === 'nutriologo') {
                    window.location.href = '{{ route("nutriologo.dashboard") }}';
                } else {
                    window.location.href = '{{ route("dashboard") }}';
                }
            }
        }
        
        // Función para cerrar sesión
        function cerrarSesion() {
            // Limpiar datos de sesión
            localStorage.removeItem('usuarioLogueado');
            sessionStorage.removeItem('usuarioLogueado');
            
            usuarioLogueado = null;
            document.getElementById('authForms').style.display = 'block';
            document.getElementById('userLoggedIn').style.display = 'none';
            document.getElementById('loginForm').reset();
            document.getElementById('registerForm').reset();
            
            // Limpiar mensajes
            document.getElementById('mensajeExitoLogin').style.display = 'none';
            document.getElementById('mensajeErrorLogin').style.display = 'none';
            document.getElementById('mensajeExitoRegistro').style.display = 'none';
            document.getElementById('mensajeErrorRegistro').style.display = 'none';
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

        // Función para validar contraseña fuerte
        function validarContrasena(contrasena) {
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/;
            return regex.test(contrasena);
        }

        // Función para mostrar errores de validación
        function mostrarError(campo, mensaje) {
            const input = document.getElementById(campo);
            const errorElement = document.getElementById(`error-${campo}`);
            
            if (input) {
                input.style.borderColor = '#d32f2f';
                input.style.backgroundColor = '#ffebee';
            }
            
            if (errorElement) {
                errorElement.textContent = mensaje;
                errorElement.style.display = 'block';
            }
        }

        // Función para limpiar errores
        function limpiarError(campo) {
            const input = document.getElementById(campo);
            const errorElement = document.getElementById(`error-${campo}`);
            
            if (input) {
                input.style.borderColor = '';
                input.style.backgroundColor = '';
            }
            
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        }

        // Validaciones en tiempo real para login
        document.getElementById('loginEmail').addEventListener('input', function() {
            const valor = this.value.trim();
            if (valor.length === 0) {
                mostrarError('loginEmail', 'El email es requerido');
            } else if (!validarEmail(valor)) {
                mostrarError('loginEmail', 'Ingrese un email válido');
            } else {
                limpiarError('loginEmail');
            }
        });

        document.getElementById('loginPassword').addEventListener('input', function() {
            const valor = this.value.trim();
            if (valor.length === 0) {
                mostrarError('loginPassword', 'La contraseña es requerida');
            } else if (valor.length < 8) {
                mostrarError('loginPassword', 'La contraseña debe tener al menos 8 caracteres');
            } else {
                limpiarError('loginPassword');
            }
        });

        // Validaciones en tiempo real para registro
        document.getElementById('registerName').addEventListener('input', function() {
            const valor = this.value.trim();
            if (valor.length === 0) {
                mostrarError('registerName', 'El nombre es requerido');
            } else if (!validarSoloLetras(valor)) {
                mostrarError('registerName', 'Solo se permiten letras y espacios');
            } else if (valor.length < 2) {
                mostrarError('registerName', 'El nombre debe tener al menos 2 caracteres');
            } else {
                limpiarError('registerName');
            }
        });

        document.getElementById('registerApellidoPaterno').addEventListener('input', function() {
            const valor = this.value.trim();
            if (valor.length === 0) {
                mostrarError('registerApellidoPaterno', 'El apellido paterno es requerido');
            } else if (!validarSoloLetras(valor)) {
                mostrarError('registerApellidoPaterno', 'Solo se permiten letras y espacios');
            } else if (valor.length < 2) {
                mostrarError('registerApellidoPaterno', 'El apellido debe tener al menos 2 caracteres');
            } else {
                limpiarError('registerApellidoPaterno');
            }
        });

        document.getElementById('registerApellidoMaterno').addEventListener('input', function() {
            const valor = this.value.trim();
            if (valor.length > 0 && !validarSoloLetras(valor)) {
                mostrarError('registerApellidoMaterno', 'Solo se permiten letras y espacios');
            } else if (valor.length > 0 && valor.length < 2) {
                mostrarError('registerApellidoMaterno', 'El apellido debe tener al menos 2 caracteres');
            } else {
                limpiarError('registerApellidoMaterno');
            }
        });

        document.getElementById('registerEmail').addEventListener('input', function() {
            const valor = this.value.trim();
            if (valor.length === 0) {
                mostrarError('registerEmail', 'El email es requerido');
            } else if (!validarEmail(valor)) {
                mostrarError('registerEmail', 'Ingrese un email válido');
            } else {
                limpiarError('registerEmail');
            }
        });

        document.getElementById('registerPassword').addEventListener('input', function() {
            const valor = this.value.trim();
            if (valor.length === 0) {
                mostrarError('registerPassword', 'La contraseña es requerida');
            } else if (!validarContrasena(valor)) {
                mostrarError('registerPassword', 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números');
            } else {
                limpiarError('registerPassword');
            }
        });

        // Función para validar formulario de login
        function validarFormularioLogin() {
            const email = document.getElementById('loginEmail').value.trim();
            const contrasena = document.getElementById('loginPassword').value.trim();
            
            let esValido = true;
            
            if (email.length === 0) {
                mostrarError('loginEmail', 'El email es requerido');
                esValido = false;
            } else if (!validarEmail(email)) {
                mostrarError('loginEmail', 'Ingrese un email válido');
                esValido = false;
            }
            
            if (contrasena.length === 0) {
                mostrarError('loginPassword', 'La contraseña es requerida');
                esValido = false;
            } else if (contrasena.length < 8) {
                mostrarError('loginPassword', 'La contraseña debe tener al menos 8 caracteres');
                esValido = false;
            }
            
            return esValido;
        }

        // Función para validar formulario de registro
        function validarFormularioRegistro() {
            const nombre = document.getElementById('registerName').value.trim();
            const apellidoPaterno = document.getElementById('registerApellidoPaterno').value.trim();
            const apellidoMaterno = document.getElementById('registerApellidoMaterno').value.trim();
            const email = document.getElementById('registerEmail').value.trim();
            const contrasena = document.getElementById('registerPassword').value.trim();
            
            let esValido = true;
            
            if (nombre.length === 0) {
                mostrarError('registerName', 'El nombre es requerido');
                esValido = false;
            } else if (!validarSoloLetras(nombre)) {
                mostrarError('registerName', 'Solo se permiten letras y espacios');
                esValido = false;
            } else if (nombre.length < 2) {
                mostrarError('registerName', 'El nombre debe tener al menos 2 caracteres');
                esValido = false;
            }
            
            if (apellidoPaterno.length === 0) {
                mostrarError('registerApellidoPaterno', 'El apellido paterno es requerido');
                esValido = false;
            } else if (!validarSoloLetras(apellidoPaterno)) {
                mostrarError('registerApellidoPaterno', 'Solo se permiten letras y espacios');
                esValido = false;
            } else if (apellidoPaterno.length < 2) {
                mostrarError('registerApellidoPaterno', 'El apellido debe tener al menos 2 caracteres');
                esValido = false;
            }
            
            if (apellidoMaterno.length > 0 && !validarSoloLetras(apellidoMaterno)) {
                mostrarError('registerApellidoMaterno', 'Solo se permiten letras y espacios');
                esValido = false;
            } else if (apellidoMaterno.length > 0 && apellidoMaterno.length < 2) {
                mostrarError('registerApellidoMaterno', 'El apellido debe tener al menos 2 caracteres');
                esValido = false;
            }
            
            if (email.length === 0) {
                mostrarError('registerEmail', 'El email es requerido');
                esValido = false;
            } else if (!validarEmail(email)) {
                mostrarError('registerEmail', 'Ingrese un email válido');
                esValido = false;
            }
            
            if (contrasena.length === 0) {
                mostrarError('registerPassword', 'La contraseña es requerida');
                esValido = false;
            } else if (!validarContrasena(contrasena)) {
                mostrarError('registerPassword', 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números');
                esValido = false;
            }
            
            return esValido;
        }

        // Validación del formulario de login
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar formulario antes de enviar
            if (!validarFormularioLogin()) {
                return;
            }
            
            const btnLogin = document.getElementById('btnLogin');
            const mensajeExitoLogin = document.getElementById('mensajeExitoLogin');
            const mensajeErrorLogin = document.getElementById('mensajeErrorLogin');
            const mensajeCargandoLogin = document.getElementById('mensajeCargandoLogin');
            
            // Ocultar todos los mensajes anteriores
            mensajeExitoLogin.style.display = 'none';
            mensajeErrorLogin.style.display = 'none';
            mensajeCargandoLogin.style.display = 'block';
            btnLogin.disabled = true;
            
            // Obtener datos del formulario
            const formData = new FormData(this);
            
            // Obtener el token CSRF del meta tag o del input hidden
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                            || document.querySelector('input[name="_token"]')?.value
                            || '{{ csrf_token() }}';
            
            // Asegurarse de que el token CSRF esté en el FormData
            if (!formData.has('_token')) {
                formData.append('_token', csrfToken);
            }
            
            // Enviar datos por AJAX
            fetch('{{ route("auth.login") }}', {
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
                // Verificar si la respuesta es JSON
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    return response.json();
                } else {
                    // Si no es JSON, puede ser un error de validación CSRF
                    throw new Error('Error de autenticación. Por favor, recarga la página e intenta de nuevo.');
                }
            })
            .then(data => {
                mensajeCargandoLogin.style.display = 'none';
                btnLogin.disabled = false;
                
                if (data.success) {
                    // Éxito - Ocultar mensaje de error si existe
                    mensajeErrorLogin.style.display = 'none';
                    mensajeExitoLogin.textContent = data.message || '¡Inicio de sesión exitoso!';
                    mensajeExitoLogin.style.display = 'block';
                    this.reset();
                    
                    // Limpiar errores
                    ['loginEmail', 'loginPassword'].forEach(campo => {
                        limpiarError(campo);
                    });
                    
                    // Redirigir inmediatamente usando la URL del servidor
                    if (data.redirect) {
                        // Usar la URL que viene del servidor (basada en la contraseña)
                        console.log('Redirigiendo a:', data.redirect);
                        window.location.href = data.redirect;
                    } else {
                        // Fallback si no hay URL
                        console.log('No hay URL de redirección, usando fallback');
                        window.location.href = '{{ route("dashboard") }}';
                    }
                } else {
                    // Error - Ocultar mensaje de éxito si existe
                    mensajeExitoLogin.style.display = 'none';
                    let errorMsg = data.message || 'Error al iniciar sesión';
                    if (data.errors && data.errors.length > 0) {
                        errorMsg += ': ' + data.errors.join(', ');
                    }
                    mensajeErrorLogin.textContent = errorMsg;
                    mensajeErrorLogin.style.display = 'block';
                    
                    // Ocultar mensaje de error después de 8 segundos
                    setTimeout(() => {
                        mensajeErrorLogin.style.display = 'none';
                    }, 8000);
                }
            })
            .catch(error => {
                mensajeCargandoLogin.style.display = 'none';
                btnLogin.disabled = false;
                
                // Ocultar mensaje de éxito si existe
                mensajeExitoLogin.style.display = 'none';
                
                console.error('Error:', error);
                mensajeErrorLogin.textContent = 'Error de conexión. Por favor, inténtalo de nuevo.';
                mensajeErrorLogin.style.display = 'block';
                
                // Ocultar mensaje de error después de 8 segundos
                setTimeout(() => {
                    mensajeErrorLogin.style.display = 'none';
                }, 8000);
            });
        });

        // Validación del formulario de registro
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar formulario antes de enviar
            if (!validarFormularioRegistro()) {
                return;
            }
            
            const btnRegistrar = document.getElementById('btnRegistrar');
            const mensajeExitoRegistro = document.getElementById('mensajeExitoRegistro');
            const mensajeErrorRegistro = document.getElementById('mensajeErrorRegistro');
            const mensajeCargandoRegistro = document.getElementById('mensajeCargandoRegistro');
            
            // Ocultar mensajes anteriores
            mensajeExitoRegistro.style.display = 'none';
            mensajeErrorRegistro.style.display = 'none';
            mensajeCargandoRegistro.style.display = 'block';
            btnRegistrar.disabled = true;
            
            // Obtener datos del formulario
            const formData = new FormData(this);
            
            // Obtener token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
            
            // Enviar datos por AJAX
            fetch('{{ route("usuarios.store") }}', {
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
                mensajeCargandoRegistro.style.display = 'none';
                btnRegistrar.disabled = false;
                
                if (data.success) {
                    // Éxito - Ocultar mensaje de error si existe
                    mensajeErrorRegistro.style.display = 'none';
                    mensajeExitoRegistro.textContent = data.message;
                    mensajeExitoRegistro.style.display = 'block';
                    this.reset();
                    
                    // Limpiar errores
                    ['registerName', 'registerApellidoPaterno', 'registerApellidoMaterno', 'registerEmail', 'registerPassword'].forEach(campo => {
                        limpiarError(campo);
                    });
                    
                    // Ocultar mensaje de éxito después de 5 segundos
                    setTimeout(() => {
                        mensajeExitoRegistro.style.display = 'none';
                    }, 5000);
                } else {
                    // Error - Ocultar mensaje de éxito si existe
                    mensajeExitoRegistro.style.display = 'none';
                    let errorMsg = data.message;
                    if (data.errors && data.errors.length > 0) {
                        errorMsg += ': ' + data.errors.join(', ');
                    }
                    mensajeErrorRegistro.textContent = errorMsg;
                    mensajeErrorRegistro.style.display = 'block';
                    
                    // Ocultar mensaje de error después de 8 segundos
                    setTimeout(() => {
                        mensajeErrorRegistro.style.display = 'none';
                    }, 8000);
                }
            })
            .catch(error => {
                mensajeCargandoRegistro.style.display = 'none';
                btnRegistrar.disabled = false;
                
                // Ocultar mensaje de éxito si existe
                mensajeExitoRegistro.style.display = 'none';
                
                console.error('Error:', error);
                let displayError = 'Error de conexión. Por favor, inténtalo de nuevo.';
                
                // Si el error es un objeto con success, usar su mensaje
                if (error && typeof error === 'object') {
                    if (error.success === false && error.message) {
                        displayError = error.message;
                    } else if (error.message) {
                        if (!error.message.includes('CSRF token mismatch')) {
                            displayError = error.message;
                        } else {
                            displayError = 'Error de seguridad (CSRF). Por favor, recarga la página e intenta de nuevo.';
                        }
                    }
                } else if (typeof error === 'string') {
                    displayError = error;
                }
                
                // No mostrar errores técnicos al usuario
                if (displayError.includes('SQLSTATE') || displayError.includes('Unknown column') || displayError.includes('Connection')) {
                    displayError = 'Error al registrar el usuario. Por favor, inténtalo de nuevo.';
                }
                
                mensajeErrorRegistro.textContent = displayError;
                mensajeErrorRegistro.style.display = 'block';
                
                // Ocultar mensaje de error después de 8 segundos
                setTimeout(() => {
                    mensajeErrorRegistro.style.display = 'none';
                }, 8000);
            });
        });
    </script>
    
    <script>
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
            // Éxito
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
            // Error
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

// El script session.js ya maneja la verificación automáticamente
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
</body>
</html>