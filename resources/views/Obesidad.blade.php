<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('CSS/style.css') }}">
    <title>Obesidad Infantil - NutriKids</title>
    <style>
        /* Estilos para centrar el menú y resaltar la pestaña activa */
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
        .menu li a[href="{{ route("obesidad") }}"] {
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
        
        /* Estilos para la sección con imagen de fondo */
        .imagen-fondo-obesidad {
            background-image: url("{{ asset('Imagenes/imagenfondoobesidad.png') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            position: relative;
        }
        
        .imagen-fondo-obesidad::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.9);
            z-index: 1;
        }
        
        .contenido-obesidad {
            position: relative;
            z-index: 2;
            padding: 60px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .contenido-obesidad h2 {
            text-align: center;
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 40px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .contenido-obesidad p {
            color: #34495e;
            line-height: 1.8;
            font-size: 1.1em;
            margin-bottom: 30px;
            text-align: justify;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
        }
        
        .contenedor-causas-consecuencias {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 40px;
        }
        
        .caja {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
        }
        
        .caja h3 {
            color: #2c3e50;
            font-size: 1.5em;
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        
        .caja ul {
            list-style: none;
            padding: 0;
        }
        
        .caja li {
            color: #34495e;
            line-height: 1.8;
            font-size: 1em;
            margin-bottom: 15px;
            padding: 10px;
            border-left: 4px solid #4CAF50;
            background-color: rgba(76, 175, 80, 0.1);
            border-radius: 0 8px 8px 0;
        }
        
        .caja li strong {
            color: #2c3e50;
            font-weight: bold;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .contenedor-causas-consecuencias {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .contenido-obesidad h2 {
                font-size: 2em;
            }
            
            .caja {
                padding: 20px;
            }
            
            .contenido-obesidad p {
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
                        <li><a href="{{ route("obesidad") }}" class="active">¿Obesidad?</a></li>
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

    <section class="imagen-fondo-obesidad">
        <div class="contenido-obesidad">
            <h2>¿Qué es la obesidad infantil?</h2>
            <p>
                La obesidad infantil es una condición médica en la que un niño tiene un exceso de grasa corporal que puede afectar su salud. 
                Se define generalmente como un exceso de peso en relación con la estatura del niño y se mide usando el índice de masa 
                corporal (IMC). Un IMC en el percentil 95 o más alto en las tablas de crecimiento estándar suele indicar obesidad.
            </p>
            <div class="contenedor-causas-consecuencias">
                <div class="caja">
                    <h3>Causas</h3>
                    <ul>
                        <li><strong>Genética:</strong> Predisposición genética al aumento de peso.</li>
                        <li><strong>Dieta:</strong> Consumo excesivo de calorías, grasas y azúcares.</li>
                        <li><strong>Actividad física:</strong> Falta de ejercicio regular.</li>
                        <li><strong>Factores psicológicos:</strong> Estrés y ansiedad pueden influir en la alimentación.</li>
                        <li><strong>Ambiente:</strong> Acceso limitado a alimentos saludables y espacios de ejercicio.</li>
                        <li><strong>Socioeconómico:</strong> Menos acceso a alimentos nutritivos.</li>
                    </ul>
                </div>
                <div class="caja">
                    <h3>Consecuencias</h3>
                    <ul>
                        <li><strong>Salud física:</strong> Riesgo de diabetes tipo 2, hipertensión y problemas ortopédicos.</li>
                        <li><strong>Salud emocional:</strong> Baja autoestima, depresión y problemas emocionales.</li>
                        <li><strong>Impacto en la vida:</strong> Dificultades en actividades físicas, rendimiento escolar y socialización.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    
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
</body>
</html>
