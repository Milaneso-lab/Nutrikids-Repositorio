import Constants from 'expo-constants';
import * as Device from 'expo-device';
import { Platform } from 'react-native';

export interface AppEnv {
  apiBaseUrl: string;
  apiVersion: string;
  apiPrefix: string;
  easProjectId: string;
  usesWebDevProxy: boolean;
}

/** Prefijo del proxy de Metro en desarrollo web (misma origen → sin CORS). */
export const WEB_DEV_API_PROXY_PREFIX = '/api-proxy';

function fromConfigOrEnv(configKey: keyof AppEnv, envKey: string, fallback: string): string {
  const extra = Constants.expoConfig?.extra as Partial<AppEnv> | undefined;
  const fromExtra = extra?.[configKey];
  if (typeof fromExtra === 'string' && fromExtra.length > 0) {
    return fromExtra;
  }

  const fromEnv = process.env[envKey];
  if (typeof fromEnv === 'string' && fromEnv.length > 0) {
    return fromEnv;
  }

  return fallback;
}

function extractPort(url: string, fallback = '8000'): string {
  const match = url.match(/:(\d+)(?:\/|$)/);
  return match?.[1] ?? fallback;
}

function isPrivateLanUrl(url: string): boolean {
  return /^https?:\/\/(192\.168\.\d{1,3}\.\d{1,3}|10\.\d{1,3}\.\d{1,3}\.\d{1,3}|172\.(1[6-9]|2\d|3[01])\.\d{1,3}\.\d{1,3})/i.test(
    url,
  );
}

function shouldUseWebDevProxy(): boolean {
  return Platform.OS === 'web' && __DEV__;
}

function resolveWebDevProxyBaseUrl(): string | null {
  if (!shouldUseWebDevProxy() || typeof window === 'undefined') {
    return null;
  }
  return `${window.location.origin}${WEB_DEV_API_PROXY_PREFIX}`.replace(/\/$/, '');
}

/**
 * Resuelve la URL base de la API según la plataforma:
 * - Web dev: proxy same-origin (/api-proxy) para evitar CORS con FastAPI.
 * - Web prod / PC: localhost (Docker expone :8000).
 * - Emulador Android: 10.0.2.2 en lugar de localhost.
 * - Nativo con localhost: IP del packager Expo (celular en la misma Wi-Fi).
 * - Nativo con IP LAN: se conserva tal cual.
 */
function resolverHostAccesible(url: string): string {
  const proxyBase = resolveWebDevProxyBaseUrl();
  if (proxyBase) {
    return proxyBase;
  }

  if (Platform.OS === 'web' && isPrivateLanUrl(url)) {
    return `http://localhost:${extractPort(url)}`;
  }

  if (Platform.OS === 'android' && !Device.isDevice) {
    if (/^https?:\/\/(localhost|127\.0\.0\.1)(:|\/|$)/i.test(url)) {
      return url.replace(/^(https?:\/\/)(localhost|127\.0\.0\.1)/i, '$110.0.2.2');
    }
  }

  if (!/^https?:\/\/(localhost|127\.0\.0\.1)(:|\/|$)/i.test(url)) {
    return url;
  }

  if (Platform.OS === 'web') {
    return url;
  }

  const hostUri =
    Constants.expoConfig?.hostUri ??
    (Constants as { expoGoConfig?: { debuggerHost?: string } }).expoGoConfig?.debuggerHost;
  const host = hostUri?.split(':')[0];

  if (!host || host === 'localhost' || host === '127.0.0.1') {
    return url;
  }

  return url.replace(/^(https?:\/\/)(localhost|127\.0\.0\.1)/i, `$1${host}`);
}

const configuredApiBaseUrl = fromConfigOrEnv('apiBaseUrl', 'EXPO_PUBLIC_API_BASE_URL', 'http://localhost:8000');
const apiVersion = fromConfigOrEnv('apiVersion', 'EXPO_PUBLIC_API_VERSION', 'v1');

export function resolveApiBaseUrl(): string {
  return resolverHostAccesible(configuredApiBaseUrl).replace(/\/$/, '');
}

export const env: AppEnv = {
  get apiBaseUrl() {
    return resolveApiBaseUrl();
  },
  apiVersion,
  get apiPrefix() {
    return `/api/${apiVersion}`;
  },
  easProjectId: fromConfigOrEnv('easProjectId', 'EXPO_PUBLIC_EAS_PROJECT_ID', ''),
  get usesWebDevProxy() {
    return shouldUseWebDevProxy() && resolveWebDevProxyBaseUrl() !== null;
  },
};

export function getApiUrl(path: string): string {
  const normalizedPath = path.startsWith('/') ? path : `/${path}`;
  return `${env.apiBaseUrl}${env.apiPrefix}${normalizedPath}`;
}

export function getApiRootUrl(): string {
  return `${env.apiBaseUrl}${env.apiPrefix}`;
}
