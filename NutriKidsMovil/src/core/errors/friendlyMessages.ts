import { AppError, AppErrorCode } from './AppError';
import { GENERIC_ERROR, isUserMessage, sanitizeMessage } from './userMessages';

const FRIENDLY_MESSAGES: Record<AppErrorCode, string> = {
  [AppErrorCode.Unknown]: GENERIC_ERROR,
  [AppErrorCode.Network]:
    'No pudimos conectar con el servidor. Comprueba que el celular está en la misma red Wi‑Fi que la PC y que NutriKids está activo.',
  [AppErrorCode.Timeout]: 'La conexión tardó demasiado. Inténtalo de nuevo.',
  [AppErrorCode.Unauthorized]: 'Correo o contraseña incorrectos.',
  [AppErrorCode.Forbidden]: 'No tienes permiso para realizar esta acción.',
  [AppErrorCode.NotFound]: 'No encontramos lo que buscabas.',
  [AppErrorCode.Validation]: 'Revisa los datos ingresados.',
  [AppErrorCode.Conflict]: 'Ya existe un registro con esos datos.',
  [AppErrorCode.Server]: GENERIC_ERROR,
  [AppErrorCode.Unavailable]: GENERIC_ERROR,
  [AppErrorCode.Cancelled]: 'La operación fue cancelada.',
};

function isGenericHttpMessage(message: string): boolean {
  return (
    message.startsWith('Request failed with status code') ||
    message === 'Network Error' ||
    message === 'Network request failed' ||
    !isUserMessage(message)
  );
}

export function getFriendlyErrorMessage(error: unknown): string {
  if (error instanceof AppError) {
    if (error.message && !isGenericHttpMessage(error.message)) {
      return sanitizeMessage(error.message, FRIENDLY_MESSAGES[error.code] ?? GENERIC_ERROR);
    }
    return FRIENDLY_MESSAGES[error.code] ?? GENERIC_ERROR;
  }

  if (error instanceof Error) {
    return sanitizeMessage(error.message);
  }

  return GENERIC_ERROR;
}
