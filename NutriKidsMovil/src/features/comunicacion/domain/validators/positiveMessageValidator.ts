import { NEGATIVE_PATTERNS } from '../../config/communication.config';

export function isPositiveMessage(content: string): boolean {
  const trimmed = content.trim();
  if (trimmed.length < 2 || trimmed.length > 500) {
    return false;
  }
  return !NEGATIVE_PATTERNS.some((pattern) => pattern.test(trimmed));
}

export function sanitizeMessage(content: string): string {
  return content.trim().slice(0, 500);
}
