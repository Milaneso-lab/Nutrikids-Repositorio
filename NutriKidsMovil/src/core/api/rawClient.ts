import axios, { AxiosError, InternalAxiosRequestConfig } from 'axios';

import { HTTP_TIMEOUT_MS } from '@core/config/constants';
import { getApiRootUrl } from '@core/config/env';
import { normalizeError } from '@core/errors/errorHandler';
import { isTransientError } from '@shared/utils/retry';

/**
 * Cliente HTTP sin interceptores de auth.
 * Usado por endpoints de autenticación y refresh para evitar ciclos de dependencia.
 */
export const rawApiClient = axios.create({
  baseURL: getApiRootUrl(),
  timeout: HTTP_TIMEOUT_MS,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

rawApiClient.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  config.baseURL = getApiRootUrl();
  if (/ngrok/i.test(config.baseURL ?? '')) {
    config.headers.set('ngrok-skip-browser-warning', '1');
  }
  return config;
});

rawApiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const originalRequest = error.config as InternalAxiosRequestConfig & { _networkRetry?: boolean };
    const normalized = normalizeError(error);

    if (
      isTransientError(normalized) &&
      originalRequest &&
      !originalRequest._networkRetry &&
      (originalRequest.method ?? 'get').toLowerCase() === 'post'
    ) {
      originalRequest._networkRetry = true;
      await new Promise((resolve) => setTimeout(resolve, 600));
      return rawApiClient(originalRequest);
    }

    return Promise.reject(normalized);
  },
);

export async function rawPost<T>(url: string, data?: unknown): Promise<T> {
  const response = await rawApiClient.post<T>(url, data);
  return response.data;
}
