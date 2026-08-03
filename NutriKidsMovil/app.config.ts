import { ExpoConfig, ConfigContext } from 'expo/config';

export default ({ config }: ConfigContext): ExpoConfig => ({
  ...config,
  name: 'NutriKids',
  slug: 'nutrikids-movil',
  version: '1.0.0',
  orientation: 'portrait',
  icon: './assets/icon.png',
  userInterfaceStyle: 'light',
  scheme: 'nutrikids',
  ios: {
    supportsTablet: true,
    bundleIdentifier: 'com.nutrikids.movil',
  },
  android: {
    adaptiveIcon: {
      backgroundColor: '#E8F5E9',
      foregroundImage: './assets/android-icon-foreground.png',
      backgroundImage: './assets/android-icon-background.png',
      monochromeImage: './assets/android-icon-monochrome.png',
    },
    package: 'com.nutrikids.movil',
    usesCleartextTraffic: true,
    softwareKeyboardLayoutMode: 'resize',
    predictiveBackGestureEnabled: false,
  },
  web: {
    favicon: './assets/favicon.png',
  },
  splash: {
    image: './assets/splash-icon.png',
    resizeMode: 'contain',
    backgroundColor: '#E8F5E9',
  },
  plugins: [
    'expo-font',
    [
      'expo-splash-screen',
      {
        backgroundColor: '#E8F5E9',
        image: './assets/splash-icon.png',
        imageWidth: 200,
      },
    ],
    [
      'expo-notifications',
      {
        icon: './assets/icon.png',
        color: '#4CAF50',
      },
    ],
    [
      'expo-camera',
      {
        cameraPermission:
          'NutriKids necesita acceso a la cámara para futuras funciones de avatar.',
      },
    ],
  ],
  extra: {
    // Inyectada en build time desde .env (EXPO_PUBLIC_*). Obligatoria para APK + Ngrok.
    apiBaseUrl: (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000').replace(/\/$/, ''),
    apiVersion: process.env.EXPO_PUBLIC_API_VERSION ?? 'v1',
    eas: {
      projectId: process.env.EXPO_PUBLIC_EAS_PROJECT_ID ?? '',
    },
  },
});
