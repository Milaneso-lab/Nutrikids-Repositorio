/** Mensajes seguros para el usuario final (sin detalles técnicos del sistema). */

export const GENERIC_ERROR = 'No se pudo completar la acción. Inténtalo de nuevo.';

const TECHNICAL =
  /HTTP|API|FastAPI|Flask|Laravel|Docker|SQL|SQLSTATE|Connection|migraci|JSON|CSRF|token|Exception|Traceback|ReferenceError|TypeError|SyntaxError|could not|Failed to fetch|NetworkError|Unknown column|base de datos|servidor|DATABASE|proxy|NUTRIKIDS|<html|502|503|504|404|500|422|23505|23503|23502|Unique violation|foreign key|not-null|column .* does not exist|Request failed with status code|Property .* doesn't exist|is not defined|undefined is not/i;

export function isUserMessage(message: string | null | undefined): boolean {
  if (!message) {
    return false;
  }
  const text = message.trim();
  if (!text || text.length > 500) {
    return false;
  }
  return !TECHNICAL.test(text);
}

export function sanitizeMessage(message: string | null | undefined, fallback = GENERIC_ERROR): string {
  if (isUserMessage(message)) {
    return String(message).trim();
  }
  return fallback;
}

/** Une varios fragmentos de error y descarta los técnicos. */
export function sanitizeMessageList(parts: string[], fallback = GENERIC_ERROR): string {
  const safe = parts.map((p) => p.trim()).filter((p) => isUserMessage(p));
  if (safe.length === 0) {
    return fallback;
  }
  return safe.join(', ');
}
