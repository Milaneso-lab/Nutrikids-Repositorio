import { calculateAge } from './age';

export const CHILD_MIN_AGE_YEARS = 2;
export const CHILD_MAX_AGE_YEARS = 20;

const ISO_DATE_REGEX = /^\d{4}-\d{2}-\d{2}$/;
const DISPLAY_DATE_REGEX = /^(\d{2})\/(\d{2})\/(\d{4})$/;

function subtractYears(from: Date, years: number): Date {
  const result = new Date(from);
  result.setFullYear(result.getFullYear() - years);
  return result;
}

function startOfToday(): Date {
  const today = new Date();
  today.setHours(12, 0, 0, 0);
  return today;
}

/** Límites para picker nativo: entre 2 y 20 años de edad. */
export function getBirthDateLimits(): { minimumDate: Date; maximumDate: Date } {
  const today = startOfToday();
  return {
    minimumDate: subtractYears(today, CHILD_MAX_AGE_YEARS),
    maximumDate: subtractYears(today, CHILD_MIN_AGE_YEARS),
  };
}

/** Inserta barras automáticamente mientras se escribe (DD/MM/AAAA). */
export function formatBirthDateInput(raw: string): string {
  const digits = raw.replace(/\D/g, '').slice(0, 8);
  if (digits.length <= 2) {
    return digits;
  }
  if (digits.length <= 4) {
    return `${digits.slice(0, 2)}/${digits.slice(2)}`;
  }
  return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
}

export function isoToDisplayDate(iso: string): string {
  if (!ISO_DATE_REGEX.test(iso)) {
    return '';
  }
  const [yyyy, mm, dd] = iso.split('-');
  return `${dd}/${mm}/${yyyy}`;
}

export function displayDateToIso(display: string): string | null {
  const match = display.match(DISPLAY_DATE_REGEX);
  if (!match) {
    return null;
  }

  const day = Number(match[1]);
  const month = Number(match[2]);
  const year = Number(match[3]);
  const iso = `${String(year).padStart(4, '0')}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

  const parsed = new Date(`${iso}T12:00:00`);
  if (
    Number.isNaN(parsed.getTime()) ||
    parsed.getFullYear() !== year ||
    parsed.getMonth() + 1 !== month ||
    parsed.getDate() !== day
  ) {
    return null;
  }

  return iso;
}

export function validateBirthDateIso(iso: string): string | null {
  if (!iso.trim()) {
    return 'La fecha de nacimiento es obligatoria';
  }

  if (!ISO_DATE_REGEX.test(iso.trim())) {
    return 'Usa el formato DD/MM/AAAA';
  }

  const date = new Date(`${iso.trim()}T12:00:00`);
  if (Number.isNaN(date.getTime())) {
    return 'Fecha no válida';
  }

  const today = startOfToday();
  if (date > today) {
    return 'La fecha no puede ser posterior a hoy';
  }

  const age = calculateAge(iso.trim());
  if (age === null) {
    return 'Fecha no válida';
  }

  if (age < CHILD_MIN_AGE_YEARS) {
    return `El niño debe tener al menos ${CHILD_MIN_AGE_YEARS} años`;
  }

  if (age > CHILD_MAX_AGE_YEARS) {
    return `El niño debe tener como máximo ${CHILD_MAX_AGE_YEARS} años`;
  }

  return null;
}
