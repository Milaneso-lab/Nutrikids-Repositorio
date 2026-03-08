<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('CSS/style.css') }}">
    <title>NutriKids - Cálculo de IMC Infantil</title>
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
        .menu li a[href="{{ route("calculadora") }}"] {
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
        
        /* Estilos para la calculadora */
        .calculadora-section {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .calculadora-section h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
        }
        
        #form-imc {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            border-color: #4CAF50;
            outline: none;
        }
        
        button[type="submit"] {
            grid-column: 1 / -1;
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
        
        #resultado-imc {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
        }
        
        #resultado-imc h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        #resultado-imc p {
            margin: 5px 0;
            color: #555;
        }
        
        /* Estilos adicionales para los resultados */
        .resultado-estado {
            margin-bottom: 15px;
            border-radius: 5px;
            padding: 15px;
        }
        
        .resultado-estado h4 {
            margin: 0 0 10px 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .resultado-estado p {
            margin: 0;
            line-height: 1.5;
        }
        
        .datos-calculo {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .datos-calculo p {
            margin: 8px 0;
            font-size: 14px;
        }
        
        .nota-importante {
            margin-top: 15px;
            padding: 10px;
            background-color: #e3f2fd;
            border-radius: 5px;
            border-left: 4px solid #2196f3;
        }
        
        .nota-importante p {
            margin: 0;
            color: #1976d2;
            font-size: 14px;
        }
        
        /* Estilos para mensajes de error */
        .error-message {
            display: none;
            color: #d32f2f;
            font-size: 12px;
            margin-top: 5px;
            font-weight: bold;
        }
        
        .form-group input.error,
        .form-group select.error {
            border-color: #d32f2f;
            background-color: #ffebee;
        }
        
        .form-group input:invalid,
        .form-group select:invalid {
            border-color: #d32f2f;
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
                        <li><a href="{{ route("calculadora") }}" class="active">Cálculo de IMC</a></li>
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

    <section class="calculadora-section">
        <h2>Calculadora de IMC Infantil</h2>
        <form id="form-imc">
            <div class="form-group">
                <label>Edad (años):</label>
                <input type="number" name="edad" placeholder="Ej: 8" min="2" max="18" step="1" required>
                <small class="error-message" id="error-edad" style="display: none; color: #d32f2f; font-size: 12px;"></small>
            </div>

            <div class="form-group">
                <label>Sexo:</label>
                <select name="sexo" required>
                    <option value="" disabled selected>Seleccione</option>
                    <option value="femenino">Femenino</option>
                    <option value="masculino">Masculino</option>
                </select>
                <small class="error-message" id="error-sexo" style="display: none; color: #d32f2f; font-size: 12px;"></small>
            </div>

            <div class="form-group">
                <label>Altura (cm):</label>
                <input type="number" name="altura" placeholder="Ej: 120" min="50" max="250" step="0.1" required>
                <small class="error-message" id="error-altura" style="display: none; color: #d32f2f; font-size: 12px;"></small>
            </div>

            <div class="form-group">
                <label>Peso (kg):</label>
                <input type="number" name="peso" placeholder="Ej: 25" min="5" max="200" step="0.1" required>
                <small class="error-message" id="error-peso" style="display: none; color: #d32f2f; font-size: 12px;"></small>
            </div>

            <button type="submit">Calcular IMC</button>
        </form>

        <div id="resultado-imc"></div>
    </section>

            <script>
        // Tablas de percentiles de IMC para niños según OMS
        const percentilesIMC = {
            femenino: {
                2: { p3: 14.3, p5: 14.5, p10: 14.8, p25: 15.3, p50: 15.9, p75: 16.6, p85: 17.1, p90: 17.5, p95: 18.2, p97: 18.7 },
                3: { p3: 14.0, p5: 14.2, p10: 14.5, p25: 15.0, p50: 15.6, p75: 16.3, p85: 16.8, p90: 17.2, p95: 17.9, p97: 18.4 },
                4: { p3: 13.8, p5: 14.0, p10: 14.3, p25: 14.8, p50: 15.4, p75: 16.1, p85: 16.6, p90: 17.0, p95: 17.7, p97: 18.2 },
                5: { p3: 13.6, p5: 13.8, p10: 14.1, p25: 14.6, p50: 15.2, p75: 15.9, p85: 16.4, p90: 16.8, p95: 17.5, p97: 18.0 },
                6: { p3: 13.5, p5: 13.7, p10: 14.0, p25: 14.5, p50: 15.1, p75: 15.8, p85: 16.3, p90: 16.7, p95: 17.4, p97: 17.9 },
                7: { p3: 13.4, p5: 13.6, p10: 13.9, p25: 14.4, p50: 15.0, p75: 15.7, p85: 16.2, p90: 16.6, p95: 17.3, p97: 17.8 },
                8: { p3: 13.3, p5: 13.5, p10: 13.8, p25: 14.3, p50: 14.9, p75: 15.6, p85: 16.1, p90: 16.5, p95: 17.2, p97: 17.7 },
                9: { p3: 13.3, p5: 13.5, p10: 13.8, p25: 14.3, p50: 14.9, p75: 15.6, p85: 16.1, p90: 16.5, p95: 17.2, p97: 17.7 },
                10: { p3: 13.4, p5: 13.6, p10: 13.9, p25: 14.4, p50: 15.0, p75: 15.7, p85: 16.2, p90: 16.6, p95: 17.3, p97: 17.8 },
                11: { p3: 13.5, p5: 13.7, p10: 14.0, p25: 14.5, p50: 15.1, p75: 15.8, p85: 16.3, p90: 16.7, p95: 17.4, p97: 17.9 },
                12: { p3: 13.7, p5: 13.9, p10: 14.2, p25: 14.7, p50: 15.3, p75: 16.0, p85: 16.5, p90: 16.9, p95: 17.6, p97: 18.1 },
                13: { p3: 14.0, p5: 14.2, p10: 14.5, p25: 15.0, p50: 15.6, p75: 16.3, p85: 16.8, p90: 17.2, p95: 17.9, p97: 18.4 },
                14: { p3: 14.4, p5: 14.6, p10: 14.9, p25: 15.4, p50: 16.0, p75: 16.7, p85: 17.2, p90: 17.6, p95: 18.3, p97: 18.8 },
                15: { p3: 14.8, p5: 15.0, p10: 15.3, p25: 15.8, p50: 16.4, p75: 17.1, p85: 17.6, p90: 18.0, p95: 18.7, p97: 19.2 },
                16: { p3: 15.2, p5: 15.4, p10: 15.7, p25: 16.2, p50: 16.8, p75: 17.5, p85: 18.0, p90: 18.4, p95: 19.1, p97: 19.6 },
                17: { p3: 15.6, p5: 15.8, p10: 16.1, p25: 16.6, p50: 17.2, p75: 17.9, p85: 18.4, p90: 18.8, p95: 19.5, p97: 20.0 },
                18: { p3: 16.0, p5: 16.2, p10: 16.5, p25: 17.0, p50: 17.6, p75: 18.3, p85: 18.8, p90: 19.2, p95: 19.9, p97: 20.4 }
            },
            masculino: {
                2: { p3: 14.4, p5: 14.6, p10: 14.9, p25: 15.4, p50: 16.0, p75: 16.7, p85: 17.2, p90: 17.6, p95: 18.3, p97: 18.8 },
                3: { p3: 14.0, p5: 14.2, p10: 14.5, p25: 15.0, p50: 15.6, p75: 16.3, p85: 16.8, p90: 17.2, p95: 17.9, p97: 18.4 },
                4: { p3: 13.7, p5: 13.9, p10: 14.2, p25: 14.7, p50: 15.3, p75: 16.0, p85: 16.5, p90: 16.9, p95: 17.6, p97: 18.1 },
                5: { p3: 13.5, p5: 13.7, p10: 14.0, p25: 14.5, p50: 15.1, p75: 15.8, p85: 16.3, p90: 16.7, p95: 17.4, p97: 17.9 },
                6: { p3: 13.4, p5: 13.6, p10: 13.9, p25: 14.4, p50: 15.0, p75: 15.7, p85: 16.2, p90: 16.6, p95: 17.3, p97: 17.8 },
                7: { p3: 13.3, p5: 13.5, p10: 13.8, p25: 14.3, p50: 14.9, p75: 15.6, p85: 16.1, p90: 16.5, p95: 17.2, p97: 17.7 },
                8: { p3: 13.3, p5: 13.5, p10: 13.8, p25: 14.3, p50: 14.9, p75: 15.6, p85: 16.1, p90: 16.5, p95: 17.2, p97: 17.7 },
                9: { p3: 13.3, p5: 13.5, p10: 13.8, p25: 14.3, p50: 14.9, p75: 15.6, p85: 16.1, p90: 16.5, p95: 17.2, p97: 17.7 },
                10: { p3: 13.4, p5: 13.6, p10: 13.9, p25: 14.4, p50: 15.0, p75: 15.7, p85: 16.2, p90: 16.6, p95: 17.3, p97: 17.8 },
                11: { p3: 13.5, p5: 13.7, p10: 14.0, p25: 14.5, p50: 15.1, p75: 15.8, p85: 16.3, p90: 16.7, p95: 17.4, p97: 17.9 },
                12: { p3: 13.7, p5: 13.9, p10: 14.2, p25: 14.7, p50: 15.3, p75: 16.0, p85: 16.5, p90: 16.9, p95: 17.6, p97: 18.1 },
                13: { p3: 14.0, p5: 14.2, p10: 14.5, p25: 15.0, p50: 15.6, p75: 16.3, p85: 16.8, p90: 17.2, p95: 17.9, p97: 18.4 },
                14: { p3: 14.4, p5: 14.6, p10: 14.9, p25: 15.4, p50: 16.0, p75: 16.7, p85: 17.2, p90: 17.6, p95: 18.3, p97: 18.8 },
                15: { p3: 14.8, p5: 15.0, p10: 15.3, p25: 15.8, p50: 16.4, p75: 17.1, p85: 17.6, p90: 18.0, p95: 18.7, p97: 19.2 },
                16: { p3: 15.2, p5: 15.4, p10: 15.7, p25: 16.2, p50: 16.8, p75: 17.5, p85: 18.0, p90: 18.4, p95: 19.1, p97: 19.6 },
                17: { p3: 15.6, p5: 15.8, p10: 16.1, p25: 16.6, p50: 17.2, p75: 17.9, p85: 18.4, p90: 18.8, p95: 19.5, p97: 20.0 },
                18: { p3: 16.0, p5: 16.2, p10: 16.5, p25: 17.0, p50: 17.6, p75: 18.3, p85: 18.8, p90: 19.2, p95: 19.9, p97: 20.4 }
            }
        };

        function interpretarIMC(imc, edad, sexo) {
            const percentiles = percentilesIMC[sexo][edad];
            if (!percentiles) {
                return {
                    estado: "No disponible",
                    descripcion: "No hay datos de referencia para esta edad y sexo.",
                    color: "#666",
                    percentil: "N/A"
                };
            }

            // Determinar el percentil específico
            let percentil = "N/A";
            if (imc < percentiles.p3) {
                percentil = "< P3";
            } else if (imc < percentiles.p5) {
                percentil = "P3-P5";
            } else if (imc < percentiles.p10) {
                percentil = "P5-P10";
            } else if (imc < percentiles.p25) {
                percentil = "P10-P25";
            } else if (imc < percentiles.p50) {
                percentil = "P25-P50";
            } else if (imc < percentiles.p75) {
                percentil = "P50-P75";
            } else if (imc < percentiles.p85) {
                percentil = "P75-P85";
            } else if (imc < percentiles.p90) {
                percentil = "P85-P90";
            } else if (imc < percentiles.p95) {
                percentil = "P90-P95";
            } else if (imc < percentiles.p97) {
                percentil = "P95-P97";
            } else {
                percentil = "> P97";
            }

            if (imc < percentiles.p3) {
                return {
                    estado: "Desnutrición Severa",
                    descripcion: "El IMC está por debajo del percentil 3, indicando desnutrición severa. Se recomienda consulta médica inmediata.",
                    color: "#d32f2f",
                    percentil: percentil
                };
            } else if (imc < percentiles.p5) {
                return {
                    estado: "Desnutrición",
                    descripcion: "El IMC está entre los percentiles 3-5, indicando desnutrición. Se recomienda evaluación nutricional.",
                    color: "#f57c00",
                    percentil: percentil
                };
            } else if (imc < percentiles.p10) {
                return {
                    estado: "Bajo Peso",
                    descripcion: "El IMC está entre los percentiles 5-10, indicando bajo peso. Se recomienda monitoreo nutricional.",
                    color: "#ff9800",
                    percentil: percentil
                };
            } else if (imc < percentiles.p25) {
                return {
                    estado: "Peso Normal (Bajo)",
                    descripcion: "El IMC está entre los percentiles 10-25, indicando peso normal pero en el rango bajo.",
                    color: "#4caf50",
                    percentil: percentil
                };
            } else if (imc < percentiles.p75) {
                return {
                    estado: "Peso Normal",
                    descripcion: "El IMC está entre los percentiles 25-75, indicando peso normal y saludable.",
                    color: "#4caf50",
                    percentil: percentil
                };
            } else if (imc < percentiles.p85) {
                return {
                    estado: "Peso Normal (Alto)",
                    descripcion: "El IMC está entre los percentiles 75-85, indicando peso normal pero en el rango alto.",
                    color: "#4caf50",
                    percentil: percentil
                };
            } else if (imc < percentiles.p90) {
                return {
                    estado: "Sobrepeso",
                    descripcion: "El IMC está entre los percentiles 85-90, indicando sobrepeso. Se recomienda evaluación nutricional.",
                    color: "#ff9800",
                    percentil: percentil
                };
            } else if (imc < percentiles.p95) {
                return {
                    estado: "Obesidad",
                    descripcion: "El IMC está entre los percentiles 90-95, indicando obesidad. Se recomienda consulta médica.",
                    color: "#f57c00",
                    percentil: percentil
                };
            } else if (imc < percentiles.p97) {
                return {
                    estado: "Obesidad Severa",
                    descripcion: "El IMC está entre los percentiles 95-97, indicando obesidad severa. Se requiere intervención médica.",
                    color: "#d32f2f",
                    percentil: percentil
                };
            } else {
                return {
                    estado: "Obesidad Mórbida",
                    descripcion: "El IMC está por encima del percentil 97, indicando obesidad mórbida. Se requiere atención médica inmediata.",
                    color: "#c62828",
                    percentil: percentil
                };
            }
        }

        // Función para limpiar errores
        function limpiarErrores() {
            const errorMessages = document.querySelectorAll('.error-message');
            const inputs = document.querySelectorAll('.form-group input, .form-group select');
            
            errorMessages.forEach(msg => msg.style.display = 'none');
            inputs.forEach(input => {
                input.classList.remove('error');
            });
        }
        
        // Función para mostrar error
        function mostrarError(campo, mensaje) {
            const errorElement = document.getElementById(`error-${campo}`);
            const inputElement = document.querySelector(`[name="${campo}"]`);
            
            errorElement.textContent = mensaje;
            errorElement.style.display = 'block';
            inputElement.classList.add('error');
        }
        
        // Función para validar datos
        function validarDatos() {
            let esValido = true;
            
            // Obtener valores
            const edad = parseInt(document.querySelector('input[name="edad"]').value);
            const sexo = document.querySelector('select[name="sexo"]').value;
            const altura = parseFloat(document.querySelector('input[name="altura"]').value);
            const peso = parseFloat(document.querySelector('input[name="peso"]').value);
            
            // Limpiar errores anteriores
            limpiarErrores();
            
            // Validar edad
            if (isNaN(edad) || edad < 2 || edad > 18) {
                mostrarError('edad', 'La edad debe estar entre 2 y 18 años');
                esValido = false;
            }
            
            // Validar sexo
            if (!sexo || sexo === '') {
                mostrarError('sexo', 'Debe seleccionar un sexo');
                esValido = false;
            }
            
            // Validar altura
            if (isNaN(altura) || altura <= 0 || altura < 50 || altura > 250) {
                mostrarError('altura', 'La altura debe estar entre 50 y 250 cm');
                esValido = false;
            }
            
            // Validar peso
            if (isNaN(peso) || peso <= 0 || peso < 5 || peso > 200) {
                mostrarError('peso', 'El peso debe estar entre 5 y 200 kg');
                esValido = false;
            }
            
            // Validaciones adicionales para valores negativos
            if (edad < 0) {
                mostrarError('edad', 'La edad no puede ser negativa');
                esValido = false;
            }
            
            if (altura < 0) {
                mostrarError('altura', 'La altura no puede ser negativa');
                esValido = false;
            }
            
            if (peso < 0) {
                mostrarError('peso', 'El peso no puede ser negativa');
                esValido = false;
            }
            
            return esValido;
        }
        
        // Función para prevenir entrada de caracteres no numéricos
        function soloNumeros(event) {
            const charCode = (event.which) ? event.which : event.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 46) {
                event.preventDefault();
                return false;
            }
            return true;
        }
        
        // Event listeners para validación en tiempo real
        document.querySelector('input[name="edad"]').addEventListener('input', function() {
            const edad = parseInt(this.value);
            const errorElement = document.getElementById('error-edad');
            
            if (isNaN(edad)) {
                errorElement.textContent = 'La edad debe ser un número válido';
                errorElement.style.display = 'block';
                this.classList.add('error');
            } else if (edad < 0) {
                errorElement.textContent = 'La edad no puede ser negativa';
                errorElement.style.display = 'block';
                this.classList.add('error');
            } else if (edad < 2 || edad > 18) {
                errorElement.textContent = 'La edad debe estar entre 2 y 18 años';
                errorElement.style.display = 'block';
                this.classList.add('error');
            } else {
                errorElement.style.display = 'none';
                this.classList.remove('error');
            }
        });
        
        document.querySelector('select[name="sexo"]').addEventListener('change', function() {
            const errorElement = document.getElementById('error-sexo');
            
            if (!this.value || this.value === '') {
                errorElement.textContent = 'Debe seleccionar un sexo';
                errorElement.style.display = 'block';
                this.classList.add('error');
            } else {
                errorElement.style.display = 'none';
                this.classList.remove('error');
            }
        });
        
        document.querySelector('input[name="altura"]').addEventListener('input', function() {
            const altura = parseFloat(this.value);
            const errorElement = document.getElementById('error-altura');
            
            if (isNaN(altura)) {
                errorElement.textContent = 'La altura debe ser un número válido';
                errorElement.style.display = 'block';
                this.classList.add('error');
            } else if (altura < 0) {
                errorElement.textContent = 'La altura no puede ser negativa';
                errorElement.style.display = 'block';
                this.classList.add('error');
            } else if (altura <= 0 || altura < 50 || altura > 250) {
                errorElement.textContent = 'La altura debe estar entre 50 y 250 cm';
                errorElement.style.display = 'block';
                this.classList.add('error');
            } else {
                errorElement.style.display = 'none';
                this.classList.remove('error');
            }
        });
        
        document.querySelector('input[name="peso"]').addEventListener('input', function() {
            const peso = parseFloat(this.value);
            const errorElement = document.getElementById('error-peso');
            
            if (isNaN(peso)) {
                errorElement.textContent = 'El peso debe ser un número válido';
                errorElement.style.display = 'block';
                this.classList.add('error');
            } else if (peso < 0) {
                errorElement.textContent = 'El peso no puede ser negativo';
                errorElement.style.display = 'block';
                this.classList.add('error');
            } else if (peso <= 0 || peso < 5 || peso > 200) {
                errorElement.textContent = 'El peso debe estar entre 5 y 200 kg';
                errorElement.style.display = 'block';
                this.classList.add('error');
            } else {
                errorElement.style.display = 'none';
                this.classList.remove('error');
            }
        });
        
        // Prevenir entrada de caracteres no válidos
        document.querySelector('input[name="edad"]').addEventListener('keypress', function(e) {
            if (e.key === '-' || e.key === 'e' || e.key === 'E') {
                e.preventDefault();
            }
        });
        
        document.querySelector('input[name="altura"]').addEventListener('keypress', function(e) {
            if (e.key === '-' || e.key === 'e' || e.key === 'E') {
                e.preventDefault();
            }
        });
        
        document.querySelector('input[name="peso"]').addEventListener('keypress', function(e) {
            if (e.key === '-' || e.key === 'e' || e.key === 'E') {
                e.preventDefault();
            }
        });
        
        document.getElementById('form-imc').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar datos antes de procesar
            if (!validarDatos()) {
                return;
            }
            
            // Obtener valores del formulario
            const edad = parseInt(document.querySelector('input[name="edad"]').value);
            const sexo = document.querySelector('select[name="sexo"]').value;
            const altura = parseFloat(document.querySelector('input[name="altura"]').value);
            const peso = parseFloat(document.querySelector('input[name="peso"]').value);
            
            // Calcular IMC
            const alturaMetros = altura / 100;
            const imc = peso / (alturaMetros * alturaMetros);
            
            // Interpretar resultado
            const interpretacion = interpretarIMC(imc, edad, sexo);
            
            // Mostrar resultado
            const resultadoDiv = document.getElementById('resultado-imc');
            resultadoDiv.innerHTML = `
                <h3>Resultado del Cálculo</h3>
                <div class="resultado-estado" style="background-color: ${interpretacion.color}20; border-left: 4px solid ${interpretacion.color};">
                    <h4 style="color: ${interpretacion.color};">${interpretacion.estado}</h4>
                    <p>${interpretacion.descripcion}</p>
                </div>
                <div class="datos-calculo">
                    <p><strong>IMC calculado:</strong> ${imc.toFixed(1)} kg/m²</p>
                    <p><strong>Percentil:</strong> ${interpretacion.percentil}</p>
                    <p><strong>Edad:</strong> ${edad} años</p>
                    <p><strong>Sexo:</strong> ${sexo.charAt(0).toUpperCase() + sexo.slice(1)}</p>
                    <p><strong>Peso:</strong> ${peso} kg</p>
                    <p><strong>Altura:</strong> ${altura} cm</p>
                </div>
                <div class="nota-importante">
                    <p>
                        <strong>Nota importante:</strong> Este cálculo es una herramienta de referencia. 
                        Para una evaluación completa y personalizada, siempre consulte a un profesional de la salud.
                    </p>
                </div>
            `;
        });
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
</script>
</body>
</html>