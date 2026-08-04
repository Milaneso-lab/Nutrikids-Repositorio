/** Buffer acotado de eventos de red/errores para diagnóstico en dispositivo (dev y producción). */

export type DiagnosticSeverity = 'info' | 'warn' | 'error';

export interface DiagnosticEvent {
  timestamp: string;
  category: 'network-error' | 'firewall';
  severity: DiagnosticSeverity;
  message: string;
  meta?: Record<string, unknown>;
}

const MAX_EVENTS = 100;
const events: DiagnosticEvent[] = [];

export function recordDiagnostic(event: Omit<DiagnosticEvent, 'timestamp'>): void {
  events.push({ ...event, timestamp: new Date().toISOString() });
  if (events.length > MAX_EVENTS) {
    events.shift();
  }
}

export function getDiagnostics(): readonly DiagnosticEvent[] {
  return events;
}

export function clearDiagnostics(): void {
  events.length = 0;
}
