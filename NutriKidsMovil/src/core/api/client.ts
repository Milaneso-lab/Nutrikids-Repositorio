import axios, { AxiosError, AxiosInstance, InternalAxiosRequestConfig } from 'axios';

import { HTTP_TIMEOUT_MS } from '@core/config/constants';
import { getApiRootUrl } from '@core/config/env';
import { AppErrorCode } from '@core/errors/AppError';
import { logError, normalizeError } from '@core/errors/errorHandler';
import { assertHostAllowed } from '@core/security/networkFirewall';
import { refreshAccessToken } from '@services/auth/tokenManager';
import { isTransientError } from '@shared/utils/retry';

import type { TokenProvider, UnauthorizedHandler } from './types';

function isAuthFailure(error: unknown): boolean {
  const normalized = normalizeError(error);
  return normalized.code === AppErrorCode.Unauthorized || normalized.statusCode === 401;
}

let tokenProvider: TokenProvider | null = null;
let unauthorizedHandler: UnauthorizedHandler | null = null;
let isRefreshing = false;
let refreshQueue: Array<{
  resolve: (token: string) => void;
  reject: (error: unknown) => void;
}> = [];

function isAuthEndpoint(url?: string): boolean {
  if (!url) {
    return false;
  }
  return url.includes('/auth/login') ||
    url.includes('/auth/register') ||
    url.includes('/auth/password/') ||
    url.includes('/auth/nino/acceso') ||
    url.includes('/auth/nino/refresh');
}

function isSessionValidationEndpoint(url?: string): boolean {
  if (!url) {
    return false;
  }
  return url.includes('/auth/me') || url.includes('/auth/nino/me');
}

function shouldForceLogoutOn401(
  originalRequest: (InternalAxiosRequestConfig & { _authRetry?: boolean }) | undefined,
): boolean {
  if (!originalRequest?._authRetry) {
    return true;
  }
  // Tras renovar el token, un 401 aquí sí indica sesión inválida; en otros endpoints suele ser falta de permiso.
  return isSessionValidationEndpoint(originalRequest.url);
}

function processRefreshQueue(error: unknown, token: string | null): void {
  refreshQueue.forEach(({ resolve, reject }) => {
    if (error || !token) {
      reject(error);
      return;
    }
    resolve(token);
  });
  refreshQueue = [];
}

export function configureAuthHandlers(handlers: {
  getAccessToken?: TokenProvider;
  onUnauthorized?: UnauthorizedHandler;
}): void {
  tokenProvider = handlers.getAccessToken ?? null;
  unauthorizedHandler = handlers.onUnauthorized ?? null;
}

function attachNgrokHeaders(config: InternalAxiosRequestConfig): void {
  const base = config.baseURL ?? getApiRootUrl();
  if (/ngrok/i.test(base)) {
    config.headers.set('ngrok-skip-browser-warning', '1');
  }
}

function attachRequestInterceptor(client: AxiosInstance): void {
  client.interceptors.request.use(async (config: InternalAxiosRequestConfig) => {
    config.baseURL = getApiRootUrl();
    assertHostAllowed(config.baseURL);
    attachNgrokHeaders(config);
    if (tokenProvider) {
      const token = await tokenProvider();
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    }
    return config;
  });
}

function attachResponseInterceptor(client: AxiosInstance): void {
  client.interceptors.response.use(
    (response) => response,
    async (error: AxiosError) => {
      const originalRequest = error.config as InternalAxiosRequestConfig & {
        _authRetry?: boolean;
        _networkRetry?: boolean;
      };
      const normalized = normalizeError(error);

      const shouldAttemptRefresh =
        normalized.statusCode === 401 &&
        originalRequest &&
        !originalRequest._authRetry &&
        !isAuthEndpoint(originalRequest.url);

      if (shouldAttemptRefresh) {
        if (isRefreshing) {
          return new Promise((resolve, reject) => {
            refreshQueue.push({
              resolve: (token: string) => {
                originalRequest.headers.Authorization = `Bearer ${token}`;
                resolve(client(originalRequest));
              },
              reject,
            });
          });
        }

        originalRequest._authRetry = true;
        isRefreshing = true;

        try {
          const newToken = await refreshAccessToken();
          processRefreshQueue(null, newToken);
          originalRequest.headers.Authorization = `Bearer ${newToken}`;
          return client(originalRequest);
        } catch (refreshError) {
          processRefreshQueue(refreshError, null);
          if (unauthorizedHandler && isAuthFailure(refreshError)) {
            await unauthorizedHandler();
          }
          return Promise.reject(normalizeError(refreshError));
        } finally {
          isRefreshing = false;
        }
      }

      if (
        isTransientError(normalized) &&
        originalRequest &&
        !originalRequest._networkRetry &&
        (originalRequest.method ?? 'get').toLowerCase() === 'get'
      ) {
        originalRequest._networkRetry = true;
        await new Promise((resolve) => setTimeout(resolve, 600));
        return client(originalRequest);
      }

      logError(normalized);

      if (
        normalized.statusCode === 401 &&
        !isAuthEndpoint(originalRequest?.url) &&
        unauthorizedHandler &&
        shouldForceLogoutOn401(originalRequest)
      ) {
        await unauthorizedHandler();
      }

      return Promise.reject(normalized);
    },
  );
}

export function createApiClient(): AxiosInstance {
  const client = axios.create({
    baseURL: getApiRootUrl(),
    timeout: HTTP_TIMEOUT_MS,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  });

  attachRequestInterceptor(client);
  attachResponseInterceptor(client);

  return client;
}

export const apiClient = createApiClient();

export async function apiGet<T>(url: string, config?: Parameters<typeof apiClient.get>[1]): Promise<T> {
  const response = await apiClient.get<T>(url, config);
  return response.data;
}

export async function apiPost<T>(
  url: string,
  data?: unknown,
  config?: Parameters<typeof apiClient.post>[2],
): Promise<T> {
  const response = await apiClient.post<T>(url, data, config);
  return response.data;
}

export async function apiPut<T>(
  url: string,
  data?: unknown,
  config?: Parameters<typeof apiClient.put>[2],
): Promise<T> {
  const response = await apiClient.put<T>(url, data, config);
  return response.data;
}

export async function apiPatch<T>(
  url: string,
  data?: unknown,
  config?: Parameters<typeof apiClient.patch>[2],
): Promise<T> {
  const response = await apiClient.patch<T>(url, data, config);
  return response.data;
}

export async function apiDelete<T>(url: string, config?: Parameters<typeof apiClient.delete>[1]): Promise<T> {
  const response = await apiClient.delete<T>(url, config);
  return response.data;
}

export { AppErrorCode };
