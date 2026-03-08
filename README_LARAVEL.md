# NutriKids - Proyecto Laravel

## 🚀 Instalación y Configuración

### 1. Instalar Dependencias

```bash
composer install
```

### 2. Configurar Base de Datos

Edita el archivo `.env` y configura la conexión a MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=NutriKids_poo
DB_USERNAME=root
DB_PASSWORD=Antonio/12
```

### 3. Ejecutar Migraciones

```bash
php artisan migrate
```

Esto creará todas las tablas necesarias:
- `users` - Usuarios del sistema
- `contactos` - Mensajes de contacto
- `comentarios` - Comentarios de usuarios
- `discusions` - Discusiones/foros

### 4. Ejecutar Seeders (Opcional)

Para poblar la base de datos con datos de ejemplo:

```bash
php artisan db:seed
```

O ejecutar seeders específicos:

```bash
php artisan db:seed --class=UsuarioSeeder
php artisan db:seed --class=ComentarioSeeder
php artisan db:seed --class=DiscusionSeeder
```

### 5. Iniciar el Servidor

```bash
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`

## 📁 Estructura del Proyecto

```
NutriKids/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php      # Autenticación
│   │       ├── ContactoController.php  # Contactos
│   │       ├── ComentarioController.php # Comentarios
│   │       ├── DiscusionController.php # Discusiones
│   │       ├── UsuarioController.php    # Registro de usuarios
│   │       └── PageController.php      # Vistas principales
│   └── Models/
│       ├── User.php
│       ├── Contacto.php
│       ├── Comentario.php
│       └── Discusion.php
├── database/
│   ├── migrations/                      # Migraciones de Laravel
│   └── seeders/                         # Seeders de Laravel
├── public/                              # Archivos públicos
│   ├── CSS/
│   │   └── style.css                    # Estilos modernos
│   ├── js/
│   │   └── session.js
│   └── Imagenes/
├── resources/
│   └── views/                           # Vistas Blade
│       ├── index.blade.php
│       ├── login.blade.php
│       ├── calculadora.blade.php
│       └── ...
└── routes/
    └── web.php                          # Rutas de la aplicación
```

## 🎨 Características del Diseño

- ✅ Header moderno y organizado
- ✅ Colores vibrantes (verde esmeralda, azul, ámbar)
- ✅ Animaciones suaves
- ✅ Diseño responsive
- ✅ Gradientes y efectos visuales modernos

## 📝 Rutas Disponibles

### Páginas Públicas
- `/` - Página principal
- `/Obesidad` - Información sobre obesidad infantil
- `/calculadora` - Calculadora de IMC
- `/nutriologos` - Información de profesionales
- `/Comentarios` - Sección de comentarios
- `/Foros` - Foros de discusión
- `/conocenos` - Acerca de nosotros
- `/login` - Inicio de sesión y registro
- `/dashboard` - Panel de usuario

### API Endpoints
- `POST /InsertarContacto` - Enviar mensaje de contacto
- `POST /InsertarComentario` - Publicar comentario
- `GET /ObtenerComentarios` - Obtener todos los comentarios
- `POST /RegistrarUsuario` - Registrar nuevo usuario
- `POST /IniciarSesion` - Iniciar sesión
- `POST /InsertarDiscusion` - Crear nueva discusión
- `GET /ObtenerDiscusiones` - Obtener todas las discusiones

## 🔄 Comandos Útiles

### Migraciones
```bash
# Crear nueva migración
php artisan make:migration nombre_migracion

# Ejecutar migraciones
php artisan migrate

# Revertir última migración
php artisan migrate:rollback

# Revertir todas las migraciones
php artisan migrate:reset
```

### Seeders
```bash
# Crear nuevo seeder
php artisan make:seeder NombreSeeder

# Ejecutar todos los seeders
php artisan db:seed

# Ejecutar seeder específico
php artisan db:seed --class=NombreSeeder
```

### Limpiar Caché
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 🔐 Seguridad

- Contraseñas hasheadas con bcrypt
- Validación de formularios en frontend y backend
- Protección CSRF en todos los formularios
- Validación de email y datos de entrada

## 📦 Tecnologías Utilizadas

- Laravel 11.x
- PHP 8.1+
- MySQL
- Blade Templates
- CSS3 Moderno con Variables CSS

## 🎯 Próximos Pasos

1. ✅ Proyecto migrado de Flask a Laravel
2. ✅ Modelos, controladores y rutas creados
3. ✅ Migraciones y seeders configurados
4. ✅ Diseño moderno aplicado
5. ⏳ Configurar autenticación completa de Laravel (opcional)

