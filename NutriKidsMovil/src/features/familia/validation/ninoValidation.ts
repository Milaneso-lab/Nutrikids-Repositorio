import type { Nino, NinoCreatePayload, NinoFormValues, NinoUpdatePayload, SexoNino } from '../types/familia.types';
import { buildAvatarConfigFromForm } from '../utils/avatarConfig';
import { validateBirthDateIso } from '../utils/birthDate';

export interface ValidationResult {
  valid: boolean;
  errors: Record<string, string>;
}

function validateRequired(value: string, label: string): string | null {
  if (!value.trim()) {
    return `${label} es obligatorio`;
  }
  return null;
}

function validateDate(value: string): string | null {
  return validateBirthDateIso(value);
}

function validateDecimal(value: string, label: string, min: number, max: number): string | null {
  if (!value.trim()) {
    return null;
  }
  const parsed = Number(value.replace(',', '.'));
  if (Number.isNaN(parsed)) {
    return `${label} debe ser un número válido`;
  }
  if (parsed < min || parsed > max) {
    return `${label} debe estar entre ${min} y ${max}`;
  }
  return null;
}

function validateSexo(value: SexoNino | ''): string | null {
  if (!value) {
    return 'Selecciona el sexo';
  }
  return null;
}

function validateNivelInicial(value: string): string | null {
  if (!value.trim()) {
    return null;
  }
  const nivel = parseInt(value, 10);
  if (Number.isNaN(nivel) || nivel < 1 || nivel > 99) {
    return 'El nivel inicial debe ser entre 1 y 99';
  }
  return null;
}

export function validateNinoForm(values: NinoFormValues): ValidationResult {
  const errors: Record<string, string> = {};

  const nombreError = validateRequired(values.nombre, 'El nombre');
  if (nombreError) {
    errors.nombre = nombreError;
  } else if (values.nombre.trim().length > 100) {
    errors.nombre = 'Máximo 100 caracteres';
  }

  const apellidosError = validateRequired(values.apellidos, 'Los apellidos');
  if (apellidosError) {
    errors.apellidos = apellidosError;
  } else if (values.apellidos.trim().length > 100) {
    errors.apellidos = 'Máximo 100 caracteres';
  }

  const fechaError = validateDate(values.fecha_nacimiento);
  if (fechaError) {
    errors.fecha_nacimiento = fechaError;
  }

  const sexoError = validateSexo(values.sexo);
  if (sexoError) {
    errors.sexo = sexoError;
  }

  const pesoError = validateDecimal(values.peso_actual_kg, 'El peso', 0, 300);
  if (pesoError) {
    errors.peso_actual_kg = pesoError;
  }

  const tallaError = validateDecimal(values.talla_actual_cm, 'La estatura', 0, 300);
  if (tallaError) {
    errors.talla_actual_cm = tallaError;
  }

  const nivelError = validateNivelInicial(values.nivelInicial);
  if (nivelError) {
    errors.nivelInicial = nivelError;
  }

  if (values.objetivoNutricional.trim().length > 500) {
    errors.objetivoNutricional = 'Máximo 500 caracteres';
  }

  return { valid: Object.keys(errors).length === 0, errors };
}

export function formValuesToCreatePayload(values: NinoFormValues, padreId: number): NinoCreatePayload {

  const peso = values.peso_actual_kg.trim() ? Number(values.peso_actual_kg.replace(',', '.')) : null;
  const talla = values.talla_actual_cm.trim() ? Number(values.talla_actual_cm.replace(',', '.')) : null;

  return {
    padre_id: padreId,
    nombre: values.nombre.trim(),
    apellidos: values.apellidos.trim(),
    fecha_nacimiento: values.fecha_nacimiento.trim(),
    sexo: values.sexo as SexoNino,
    peso_actual_kg: peso,
    talla_actual_cm: talla,
    avatar_config: buildAvatarConfigFromForm(values.avatar, values.objetivoNutricional, values.nivelInicial),
  };
}

export function formValuesToUpdatePayload(values: NinoFormValues): NinoUpdatePayload {

  const peso = values.peso_actual_kg.trim() ? Number(values.peso_actual_kg.replace(',', '.')) : null;
  const talla = values.talla_actual_cm.trim() ? Number(values.talla_actual_cm.replace(',', '.')) : null;

  return {
    nombre: values.nombre.trim(),
    apellidos: values.apellidos.trim(),
    fecha_nacimiento: values.fecha_nacimiento.trim(),
    sexo: values.sexo as SexoNino,
    peso_actual_kg: peso,
    talla_actual_cm: talla,
    avatar_config: buildAvatarConfigFromForm(values.avatar, values.objetivoNutricional, values.nivelInicial),
  };
}

export function ninoToFormValues(nino: Nino): NinoFormValues {
  const avatar = nino.avatar_config ?? {};
  return {
    nombre: nino.nombre,
    apellidos: nino.apellidos,
    fecha_nacimiento: nino.fecha_nacimiento,
    sexo: nino.sexo,
    peso_actual_kg: nino.peso_actual_kg != null ? String(nino.peso_actual_kg) : '',
    talla_actual_cm: nino.talla_actual_cm != null ? String(nino.talla_actual_cm) : '',
    objetivoNutricional: avatar.objetivoNutricional ?? '',
    nivelInicial: avatar.nivelInicial != null ? String(avatar.nivelInicial) : '',
    avatar: {
      emoji: avatar.emoji,
      backgroundColor: avatar.backgroundColor,
      photoUri: avatar.photoUri,
    },
  };
}

export const EMPTY_NINO_FORM: NinoFormValues = {
  nombre: '',
  apellidos: '',
  fecha_nacimiento: '',
  sexo: '',
  peso_actual_kg: '',
  talla_actual_cm: '',
  objetivoNutricional: '',
  nivelInicial: '1',
  avatar: { emoji: '🧒', backgroundColor: '#E8F5E9' },
};
