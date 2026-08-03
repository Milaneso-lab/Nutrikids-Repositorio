export function assertNever(value: never): never {
  throw new Error(`Valor inesperado: ${String(value)}`);
}

export function assertDefined<T>(value: T | null | undefined, message: string): T {
  if (value === null || value === undefined) {
    throw new Error(message);
  }
  return value;
}
