import {
  CHILD_MAX_AGE_YEARS,
  CHILD_MIN_AGE_YEARS,
  displayDateToIso,
  formatBirthDateInput,
  isoToDisplayDate,
  validateBirthDateIso,
} from '../birthDate';

describe('birthDate', () => {
  describe('formatBirthDateInput', () => {
    it('inserta barras automáticamente', () => {
      expect(formatBirthDateInput('1')).toBe('1');
      expect(formatBirthDateInput('15')).toBe('15');
      expect(formatBirthDateInput('150')).toBe('15/0');
      expect(formatBirthDateInput('1503')).toBe('15/03');
      expect(formatBirthDateInput('150320')).toBe('15/03/20');
      expect(formatBirthDateInput('15032018')).toBe('15/03/2018');
    });

    it('ignora caracteres no numéricos', () => {
      expect(formatBirthDateInput('15/03/2018')).toBe('15/03/2018');
      expect(formatBirthDateInput('15-03-2018')).toBe('15/03/2018');
    });
  });

  describe('displayDateToIso', () => {
    it('convierte DD/MM/AAAA a ISO', () => {
      expect(displayDateToIso('15/03/2018')).toBe('2018-03-15');
    });

    it('rechaza fechas inválidas', () => {
      expect(displayDateToIso('31/02/2018')).toBeNull();
      expect(displayDateToIso('15/03')).toBeNull();
    });
  });

  describe('isoToDisplayDate', () => {
    it('convierte ISO a DD/MM/AAAA', () => {
      expect(isoToDisplayDate('2018-03-15')).toBe('15/03/2018');
    });
  });

  describe('validateBirthDateIso', () => {
    it('rechaza fechas futuras', () => {
      const future = new Date();
      future.setFullYear(future.getFullYear() + 1);
      const iso = future.toISOString().slice(0, 10);
      expect(validateBirthDateIso(iso)).toMatch(/posterior|hoy/i);
    });

    it('rechaza menores de 2 años', () => {
      const recent = new Date();
      recent.setFullYear(recent.getFullYear() - 1);
      const iso = recent.toISOString().slice(0, 10);
      expect(validateBirthDateIso(iso)).toBe(`El niño debe tener al menos ${CHILD_MIN_AGE_YEARS} años`);
    });

    it('rechaza mayores de 20 años', () => {
      const old = new Date();
      old.setFullYear(old.getFullYear() - 21);
      const iso = old.toISOString().slice(0, 10);
      expect(validateBirthDateIso(iso)).toBe(`El niño debe tener como máximo ${CHILD_MAX_AGE_YEARS} años`);
    });

    it('acepta edad dentro del rango', () => {
      const valid = new Date();
      valid.setFullYear(valid.getFullYear() - 8);
      const iso = valid.toISOString().slice(0, 10);
      expect(validateBirthDateIso(iso)).toBeNull();
    });
  });
});
