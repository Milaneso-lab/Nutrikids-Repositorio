<form id="contactoForm" action="{{ route("contacto.store") }}" method="post">
                    @csrf
    <input type="text" id="nombre" name="nombre" required placeholder="Nombre" maxlength="50" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+" title="Solo letras y espacios permitidos">
    <input type="text" id="apellido" name="apellido" required placeholder="Apellido" maxlength="50" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+" title="Solo letras y espacios permitidos">
    <input type="email" id="email" name="email" required placeholder="Correo electrónico" maxlength="100" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Ingrese un email válido (ejemplo: usuario@dominio.com)">
    <textarea id="mensaje" name="mensaje" required placeholder="Mensaje" maxlength="500" minlength="10" title="El mensaje debe tener entre 10 y 500 caracteres"></textarea>
    <button type="submit" id="btnEnviar">Enviar</button>
    <div id="mensajeExito" style="display:none; color: #4CAF50; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #d4edda; border-radius: 5px;"></div>
    <div id="mensajeError" style="display:none; color: #721c24; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #f8d7da; border-radius: 5px;"></div>
    <div id="mensajeCargando" style="display:none; color: #856404; margin-top: 10px; font-weight: bold; padding: 10px; background-color: #fff3cd; border-radius: 5px;">Enviando mensaje...</div>
</form>

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
    const input = document.getElementById(campo);
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
    const input = document.getElementById(campo);
    input.style.borderColor = '';
    input.style.backgroundColor = '';
    
    const errorDiv = document.getElementById(`error-${campo}`);
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

// Validaciones en tiempo real
document.getElementById('nombre').addEventListener('input', function() {
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

document.getElementById('apellido').addEventListener('input', function() {
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

document.getElementById('email').addEventListener('input', function() {
    const valor = this.value.trim();
    if (valor.length === 0) {
        mostrarError('email', 'El email es requerido');
    } else if (!validarEmail(valor)) {
        mostrarError('email', 'Ingrese un email válido (ejemplo: usuario@dominio.com)');
    } else {
        limpiarError('email');
    }
});

document.getElementById('mensaje').addEventListener('input', function() {
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
    const nombre = document.getElementById('nombre').value.trim();
    const apellido = document.getElementById('apellido').value.trim();
    const email = document.getElementById('email').value.trim();
    const mensaje = document.getElementById('mensaje').value.trim();
    
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