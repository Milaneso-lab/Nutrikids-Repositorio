# Credenciales por perfil (NutriKids)

## 📋 Acceso por rol

Después de ejecutar `php artisan migrate:fresh --seed`, puedes usar estas cuentas para probar cada dashboard.

| Rol | Correo | Contraseña | Dashboard (después de login) |
|-----|--------|------------|-----------------------------|
| **Administrador** | `admin@temp.com` | `admin123` | `/admin/dashboard` |
| **Administrador** | `admin@nutrikids.com` | `admin123` | `/admin/dashboard` |
| **Nutriólogo** | `nutriologo@nutrikids.com` | `nutriologo123` | `/nutriologo/dashboard` |
| **Padre** | `padre@nutrikids.com` | `padre123` | `/dashboard` |

### Administrador (panel admin)
- **Email:** `admin@temp.com` o `admin@nutrikids.com`
- **Contraseña:** `admin123`

## 🚀 Crear/Actualizar Credenciales Temporales

Tienes dos formas de crear o actualizar las credenciales temporales:

### Opción 1: Usando Artisan Command
```bash
php artisan admin:crear-temporal
```

### Opción 2: Usando Seeder
```bash
php artisan db:seed --class=AdminTemporalSeeder
```

## ⚠️ Importante

- Estas son credenciales **TEMPORALES** para desarrollo
- **Cámbialas** desde el panel de administración una vez que tengas acceso
- Desde el panel de administración podrás crear otros usuarios (administradores, nutriólogos, padres)

## 📍 Acceso al Panel

1. Ve a la página de Login: `/login`
2. Ingresa las credenciales temporales
3. Serás redirigido automáticamente al panel de administración
4. Desde ahí podrás crear otros perfiles de usuario

## 🔐 Método de Validación

El sistema ahora valida usuarios de la siguiente manera:

1. **Verifica la existencia del usuario** por correo electrónico en la base de datos
2. **Valida la contraseña** usando Hash::check()
3. **Inicia sesión** usando Laravel Auth
4. **Redirige según el rol:**
   - **PADRE:** Mantiene en interfaces básicas (`/`)
   - **ADMINISTRADOR:** Redirige a `/admin/dashboard`
   - **NUTRIOLOGO:** Redirige a `/nutriologo/dashboard`






