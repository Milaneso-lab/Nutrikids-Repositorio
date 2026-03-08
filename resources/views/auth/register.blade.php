<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registro - NutriKids</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #4CAF50;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 18px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .login-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .loading {
            display: none;
            text-align: center;
            color: #666;
            margin-top: 10px;
        }

        .password-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>🌱 NutriKids</h1>
            <p>Crear Cuenta</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf

            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input 
                    type="text" 
                    id="nombre" 
                    name="nombre" 
                    value="{{ old('nombre') }}" 
                    required 
                    autofocus
                    placeholder="Tu nombre"
                >
                @error('nombre')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="apellido_paterno">Apellido Paterno</label>
                <input 
                    type="text" 
                    id="apellido_paterno" 
                    name="apellido_paterno" 
                    value="{{ old('apellido_paterno') }}" 
                    required
                    placeholder="Tu apellido paterno"
                >
                @error('apellido_paterno')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="apellido_materno">Apellido Materno (Opcional)</label>
                <input 
                    type="text" 
                    id="apellido_materno" 
                    name="apellido_materno" 
                    value="{{ old('apellido_materno') }}"
                    placeholder="Tu apellido materno"
                >
                @error('apellido_materno')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required
                    placeholder="tu@email.com"
                >
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="password-wrapper">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="Mínimo 8 caracteres"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                </div>
                <div class="password-hint">
                    Debe contener al menos: 8 caracteres, una mayúscula, una minúscula y un número
                </div>
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar Contraseña</label>
                <div class="password-wrapper">
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required
                        placeholder="Repite tu contraseña"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                Registrarse
            </button>

            <div class="loading" id="loading">
                Creando cuenta...
            </div>
        </form>

        <div class="login-link">
            <p>¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión aquí</a></p>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleBtn = passwordInput.nextElementSibling;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.getElementById('loading');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creando cuenta...';
            loading.style.display = 'block';
        });
    </script>
</body>
</html>

