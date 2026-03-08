<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión - NutriKids</title>
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

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 40px 40px 30px;
            text-align: center;
        }

        .login-header .logo-img {
            max-width: 80px;
            margin-bottom: 15px;
        }

        .login-header h1 {
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.95;
        }

        .login-body {
            padding: 40px;
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

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .remember-me input {
            margin-right: 8px;
            width: auto;
        }

        .remember-me label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
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

        .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .register-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Bienvenido</h1>
            <p>Inicia sesión en tu cuenta</p>
        </div>

        <div class="login-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            <div class="alert alert-error" id="alertError" style="display: none;"></div>

            <form method="POST" action="{{ route('auth.login') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus
                        placeholder="tu@email.com"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="contrasena" 
                            required
                            placeholder="Tu contraseña"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword()">👁️</button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    Iniciar Sesión
                </button>

                <div class="loading" id="loading">
                    Iniciando sesión...
                </div>
            </form>

            <div class="register-link">
                <p>¿No tienes cuenta? <a href="{{ url('/') }}">Regístrate aquí</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const loading = document.getElementById('loading');
            const alertError = document.getElementById('alertError');

            submitBtn.disabled = true;
            submitBtn.textContent = 'Iniciando sesión...';
            loading.style.display = 'block';
            alertError.style.display = 'none';

            const formData = new FormData(this);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            if (!formData.has('_token')) formData.append('_token', csrfToken);

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
            .then(function(res) {
                if (res.headers.get('content-type') && res.headers.get('content-type').includes('application/json')) {
                    return res.json();
                }
                throw new Error('Error de conexión. Recarga la página e intenta de nuevo.');
            })
            .then(function(data) {
                loading.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Iniciar Sesión';

                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else if (!data.success) {
                    alertError.textContent = data.message || 'Email o contraseña incorrectos.';
                    if (data.errors && data.errors.length) {
                        alertError.textContent += ' ' + data.errors.join(' ');
                    }
                    alertError.style.display = 'block';
                }
            })
            .catch(function(err) {
                loading.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Iniciar Sesión';
                alertError.textContent = err.message || 'Error de conexión. Inténtalo de nuevo.';
                alertError.style.display = 'block';
            });
        });
    </script>
</body>
</html>

