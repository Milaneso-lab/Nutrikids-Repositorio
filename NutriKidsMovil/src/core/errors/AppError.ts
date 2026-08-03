export enum AppErrorCode {
  Unknown = 'UNKNOWN',
  Network = 'NETWORK',
  Timeout = 'TIMEOUT',
  Unauthorized = 'UNAUTHORIZED',
  Forbidden = 'FORBIDDEN',
  NotFound = 'NOT_FOUND',
  Validation = 'VALIDATION',
  Conflict = 'CONFLICT',
  Server = 'SERVER',
  Unavailable = 'UNAVAILABLE',
  Cancelled = 'CANCELLED',
}

export class AppError extends Error {
  readonly code: AppErrorCode;
  readonly statusCode?: number;
  readonly details?: unknown;
  readonly isOperational: boolean;

  constructor(
    message: string,
    code: AppErrorCode = AppErrorCode.Unknown,
    options?: {
      statusCode?: number;
      details?: unknown;
      cause?: unknown;
      isOperational?: boolean;
    },
  ) {
    super(message, { cause: options?.cause });
    this.name = 'AppError';
    this.code = code;
    this.statusCode = options?.statusCode;
    this.details = options?.details;
    this.isOperational = options?.isOperational ?? true;
  }
}

export function isAppError(error: unknown): error is AppError {
  return error instanceof AppError;
}
