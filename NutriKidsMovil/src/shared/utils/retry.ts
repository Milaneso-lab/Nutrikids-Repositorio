import { AppError, AppErrorCode, isAppError } from '@core/errors/AppError';
import { normalizeError } from '@core/errors/errorHandler';

export interface RetryOptions {
  retries?: number;
  delayMs?: number;
  backoff?: number;
  retryIf?: (error: unknown) => boolean;
}

export function isTransientError(error: unknown): boolean {
  const normalized = isAppError(error) ? error : normalizeError(error);

  return (
    normalized.code === AppErrorCode.Network ||
    normalized.code === AppErrorCode.Timeout ||
    normalized.code === AppErrorCode.Unavailable ||
    normalized.code === AppErrorCode.Server ||
    normalized.statusCode === 429
  );
}

export async function withRetry<T>(
  operation: () => Promise<T>,
  { retries = 3, delayMs = 500, backoff = 1.5, retryIf = isTransientError }: RetryOptions = {},
): Promise<T> {
  let attempt = 0;
  let wait = delayMs;

  while (true) {
    try {
      return await operation();
    } catch (error) {
      if (attempt >= retries || !retryIf(error)) {
        throw error;
      }
      attempt += 1;
      await new Promise((resolve) => setTimeout(resolve, wait));
      wait = Math.round(wait * backoff);
    }
  }
}
