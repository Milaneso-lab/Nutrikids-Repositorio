/**
 * Firewall de red del cliente: ninguna petición HTTP sale de la app hacia un host
 * fuera de esta allowlist (backend propio, IPs privadas/emulador en dev, túneles ngrok/Railway).
 */
import { recordDiagnostic } from '@core/monitoring/diagnostics';

const ALLOWED_HOST_PATTERNS: RegExp[] = [
  /^localhost$/i,
  /^127\.0\.0\.1$/,
  /^10\.0\.2\.2$/, // loopback del emulador Android
  /^192\.168\.\d{1,3}\.\d{1,3}$/, // LAN dev
  /^10\.\d{1,3}\.\d{1,3}\.\d{1,3}$/,
  /^172\.(1[6-9]|2\d|3[01])\.\d{1,3}\.\d{1,3}$/,
  /\.ngrok(-free)?\.app$/i,
  /\.ngrok\.io$/i,
  /\.up\.railway\.app$/i,
];

function isHostAllowed(host: string): boolean {
  return ALLOWED_HOST_PATTERNS.some((pattern) => pattern.test(host));
}

/** Lanza si `url` apunta a un host no autorizado; registra el intento en cualquier caso. */
export function assertHostAllowed(url: string): void {
  let host: string;
  try {
    host = new URL(url).hostname;
  } catch {
    recordDiagnostic({
      category: 'firewall',
      severity: 'error',
      message: 'URL de solicitud inválida bloqueada por el firewall de red',
      meta: { url },
    });
    throw new Error('URL de solicitud inválida bloqueada por el firewall de red.');
  }

  const allowed = isHostAllowed(host);

  recordDiagnostic({
    category: 'firewall',
    severity: allowed ? 'info' : 'error',
    message: allowed ? `Host permitido: ${host}` : `Host bloqueado: ${host}`,
    meta: { host, url, blocked: !allowed },
  });

  if (!allowed) {
    if (typeof __DEV__ !== 'undefined' && __DEV__) {
      console.warn('[Firewall:BLOCKED]', host, url);
    }
    throw new Error(`Host no autorizado bloqueado por el firewall de red: ${host}`);
  }
}
