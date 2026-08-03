# NutriKids Móvil

Aplicación React Native (Expo) para la experiencia gamificada de NutriKids.

## Inicio rápido (demo)

```bash
cd NutriKidsMovil
cp .env.example .env    # DEMO_MODE=true por defecto
npm install
npm start
```

Escanea el QR con **Expo Go** (Android/iOS) o pulsa `a` para emulador Android.

### Credenciales demo

| Campo | Valor |
|---|---|
| Correo | `demo@nutrikids.app` |
| Contraseña | `Demo1234` |
| Código reset | `123456` |

Con `EXPO_PUBLIC_DEMO_MODE=true` **no se necesita la API** para navegar toda la app.

## Scripts

| Comando | Descripción |
|---|---|
| `npm start` | Metro bundler (Expo) |
| `npm run android` | Abrir en Android |
| `npm run ios` | Abrir en iOS (macOS) |
| `npm run typecheck` | Verificación TypeScript |

## Variables de entorno

| Variable | Default | Descripción |
|---|---|---|
| `EXPO_PUBLIC_DEMO_MODE` | `true` | Datos simulados sin API |
| `EXPO_PUBLIC_API_BASE_URL` | `http://localhost:8000` | URL FastAPI (Android emu: `10.0.2.2:8000`) |
| `EXPO_PUBLIC_API_VERSION` | `v1` | Versión REST |

Para usar la API real: `EXPO_PUBLIC_DEMO_MODE=false` en `.env`.

## Flujo navegable en demo

1. Splash → Onboarding (1ª vez) → Bienvenida
2. Login / Registro / Recuperar contraseña
3. Tab bar: Inicio, Hábitos, Retos, Avatar, Logros
4. Modo padre (FAB + modal)
5. Logout desde header
