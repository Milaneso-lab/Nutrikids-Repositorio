import { GENERIC_ERROR, isUserMessage, sanitizeMessage, sanitizeMessageList } from '../userMessages';

describe('userMessages', () => {
  it('acepta mensajes de validación comprensibles', () => {
    expect(isUserMessage('El email no tiene un formato válido.')).toBe(true);
    expect(isUserMessage('Revisa los datos ingresados.')).toBe(true);
  });

  it('rechaza mensajes técnicos del sistema', () => {
    expect(isUserMessage('Sin conexión con el servidor. Verifica FastAPI.')).toBe(false);
    expect(isUserMessage('No hay conexión con la base de datos.')).toBe(false);
    expect(isUserMessage('Request failed with status code 500')).toBe(false);
    expect(isUserMessage('SQLSTATE[23505]: Unique violation')).toBe(false);
  });

  it('sustituye mensajes técnicos por genérico', () => {
    expect(sanitizeMessage('HTTP 503 Service Unavailable')).toBe(GENERIC_ERROR);
    expect(sanitizeMessage('Nombre obligatorio', GENERIC_ERROR)).toBe('Nombre obligatorio');
  });

  it('filtra listas mezclando validaciones y errores técnicos', () => {
    expect(
      sanitizeMessageList(['El teléfono debe tener 10 dígitos', 'SQLSTATE connection refused']),
    ).toBe('El teléfono debe tener 10 dígitos');
    expect(sanitizeMessageList(['HTTP 500', 'DATABASE_UNAVAILABLE'])).toBe(GENERIC_ERROR);
  });
});
