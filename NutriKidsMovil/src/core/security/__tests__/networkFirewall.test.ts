import { assertHostAllowed } from '../networkFirewall';

describe('networkFirewall', () => {
  it('permite el host de producción en Railway', () => {
    expect(() => assertHostAllowed('https://nutrikids-sitioweb.up.railway.app/api/v1')).not.toThrow();
  });

  it('permite hosts de desarrollo (localhost, emulador, LAN)', () => {
    expect(() => assertHostAllowed('http://localhost:8000/api/v1')).not.toThrow();
    expect(() => assertHostAllowed('http://10.0.2.2:8000/api/v1')).not.toThrow();
    expect(() => assertHostAllowed('http://192.168.1.50:8000/api/v1')).not.toThrow();
  });

  it('bloquea hosts no autorizados', () => {
    expect(() => assertHostAllowed('https://evil.example.com/api/v1')).toThrow(/no autorizado/);
  });

  it('bloquea URLs inválidas', () => {
    expect(() => assertHostAllowed('no-es-una-url')).toThrow();
  });
});
