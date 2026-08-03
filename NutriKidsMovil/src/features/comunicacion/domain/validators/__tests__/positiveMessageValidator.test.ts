import { isPositiveMessage, sanitizeMessage } from '../positiveMessageValidator';

describe('positiveMessageValidator', () => {
  it('acepta mensajes positivos', () => {
    expect(isPositiveMessage('¡Muy bien hecho!')).toBe(true);
  });

  it('rechaza mensajes vacíos o demasiado cortos', () => {
    expect(isPositiveMessage('')).toBe(false);
    expect(isPositiveMessage(' ')).toBe(false);
    expect(isPositiveMessage('a')).toBe(false);
  });

  it('rechaza patrones negativos', () => {
    expect(isPositiveMessage('Eres perezoso')).toBe(false);
    expect(isPositiveMessage('Te castigo')).toBe(false);
  });

  it('sanitiza y limita longitud', () => {
    const long = 'a'.repeat(600);
    expect(sanitizeMessage(`  ${long}  `).length).toBe(500);
  });
});
