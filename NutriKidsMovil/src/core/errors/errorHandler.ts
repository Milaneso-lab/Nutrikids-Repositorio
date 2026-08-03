import axios from 'axios';

import { AppError, AppErrorCode } from './AppError';
import { GENERIC_ERROR, sanitizeMessage, sanitizeMessageList } from './userMessages';

interface ApiErrorBody {
  // Contrato de la API v1: { error: { code, message, details: [{ field, issue }] } }
  error?: { code?: string; message?: string; details?: Array<{ field?: string; issue?: string }> };
  detail?: string | Array<{ msg?: string }>;
  message?: string;
  errors?: string[];
}

function extractMessage(data: unknown, fallback: string): string {
  if (!data || typeof data !== 'object') {
    return sanitizeMessage(fallback);
  }

  const body = data as ApiErrorBody;

  if (Array.isArray(body.errors) && body.errors.length > 0) {
    return sanitizeMessageList(body.errors.map(String), sanitizeMessage(fallback));
  }

  if (typeof body.error?.message === 'string' && body.error.message.length > 0) {
    const detalles = (body.error.details ?? [])
      .map((d) => d.issue)
      .filter((issue): issue is string => Boolean(issue));

    if (detalles.length > 0) {
      return sanitizeMessageList(detalles, sanitizeMessage(body.error.message, sanitizeMessage(fallback)));
    }

    return sanitizeMessage(body.error.message, sanitizeMessage(fallback));
  }

  if (typeof body.message === 'string') {
    return sanitizeMessage(body.message, sanitizeMessage(fallback));
  }

  if (typeof body.detail === 'string') {
    return sanitizeMessage(body.detail, sanitizeMessage(fallback));
  }

  if (Array.isArray(body.detail) && body.detail.length > 0) {
    const partes = body.detail
      .map((item) => (item && typeof item.msg === 'string' ? item.msg : null))
      .filter((msg): msg is string => Boolean(msg));

    if (partes.length > 0) {
      return sanitizeMessageList(partes, sanitizeMessage(fallback));
    }
  }

  return sanitizeMessage(fallback);
}

export function normalizeError(error: unknown): AppError {
  if (error instanceof AppError) {
    return error;
  }

  if (axios.isCancel(error)) {
    return new AppError('La operación fue cancelada', AppErrorCode.Cancelled, { cause: error });
  }

  if (axios.isAxiosError(error)) {
    if (error.code === 'ECONNABORTED') {
      return new AppError(GENERIC_ERROR, AppErrorCode.Timeout, { cause: error });
    }

    if (!error.response) {
      return new AppError(GENERIC_ERROR, AppErrorCode.Network, { cause: error });
    }

    const status = error.response.status;
    const message = extractMessage(error.response.data, GENERIC_ERROR);

    if (status === 401) {
      return new AppError(message, AppErrorCode.Unauthorized, { statusCode: status, cause: error });
    }
    if (status === 403) {
      return new AppError(message, AppErrorCode.Forbidden, { statusCode: status, cause: error });
    }
    if (status === 404) {
      return new AppError(message, AppErrorCode.NotFound, { statusCode: status, cause: error });
    }
    if (status === 400 || status === 422) {
      return new AppError(message, AppErrorCode.Validation, {
        statusCode: status,
        details: error.response.data,
        cause: error,
      });
    }
    if (status === 409) {
      return new AppError(message, AppErrorCode.Conflict, {
        statusCode: status,
        details: error.response.data,
        cause: error,
      });
    }
    if (status === 503) {
      return new AppError(GENERIC_ERROR, AppErrorCode.Unavailable, { statusCode: status, cause: error });
    }
    if (status >= 500) {
      return new AppError(GENERIC_ERROR, AppErrorCode.Server, {
        statusCode: status,
        cause: error,
      });
    }

    return new AppError(message, AppErrorCode.Unknown, { statusCode: status, cause: error });
  }

  if (error instanceof Error) {
    return new AppError(sanitizeMessage(error.message), AppErrorCode.Unknown, { cause: error });
  }

  return new AppError(GENERIC_ERROR, AppErrorCode.Unknown, { cause: error });
}

export function logError(error: AppError): void {
  if (__DEV__) {
    console.error('[NutriKids Error]', {
      code: error.code,
      message: error.message,
      statusCode: error.statusCode,
      details: error.details,
    });
  }
}
