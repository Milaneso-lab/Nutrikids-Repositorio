export interface ValidationResult {
  valid: boolean;
  errors: Record<string, string>;
}

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export const PASSWORD_MIN_LENGTH = 8;

export function validateEmail(email: string): string | null {
  const trimmed = email.trim();
  if (!trimmed) {
    return 'El correo es obligatorio';
  }
  if (!EMAIL_REGEX.test(trimmed)) {
    return 'Ingresa un correo válido';
  }
  return null;
}

export function validatePassword(password: string): string[] {
  const issues: string[] = [];
  if (password.length < PASSWORD_MIN_LENGTH) {
    issues.push(`Mínimo ${PASSWORD_MIN_LENGTH} caracteres`);
  }
  if (!/[A-Z]/.test(password)) {
    issues.push('Al menos una mayúscula');
  }
  if (!/[a-z]/.test(password)) {
    issues.push('Al menos una minúscula');
  }
  if (!/\d/.test(password)) {
    issues.push('Al menos un número');
  }
  return issues;
}

export function validatePasswordField(password: string): string | null {
  if (!password) {
    return 'La contraseña es obligatoria';
  }
  const issues = validatePassword(password);
  if (issues.length > 0) {
    return issues.join('. ');
  }
  return null;
}

export function validateRequired(value: string, fieldLabel: string): string | null {
  if (!value.trim()) {
    return `${fieldLabel} es obligatorio`;
  }
  return null;
}

export interface LoginFormValues {
  email: string;
  contrasena: string;
}

export function validateLoginForm(values: LoginFormValues): ValidationResult {
  const errors: Record<string, string> = {};
  const emailError = validateEmail(values.email);
  if (emailError) {
    errors.email = emailError;
  }
  if (!values.contrasena) {
    errors.contrasena = 'La contraseña es obligatoria';
  }
  return { valid: Object.keys(errors).length === 0, errors };
}

export interface RegisterFormValues {
  nombre: string;
  apellido_paterno: string;
  apellido_materno: string;
  email: string;
  contrasena: string;
  confirmarContrasena: string;
}

export function validateRegisterForm(values: RegisterFormValues): ValidationResult {
  const errors: Record<string, string> = {};

  const nombreError = validateRequired(values.nombre, 'El nombre');
  if (nombreError) {
    errors.nombre = nombreError;
  }

  const apellidoError = validateRequired(values.apellido_paterno, 'El apellido paterno');
  if (apellidoError) {
    errors.apellido_paterno = apellidoError;
  }

  const emailError = validateEmail(values.email);
  if (emailError) {
    errors.email = emailError;
  }

  const passwordError = validatePasswordField(values.contrasena);
  if (passwordError) {
    errors.contrasena = passwordError;
  }

  if (values.contrasena !== values.confirmarContrasena) {
    errors.confirmarContrasena = 'Las contraseñas no coinciden';
  }

  return { valid: Object.keys(errors).length === 0, errors };
}

export function validateForgotPasswordForm(email: string): ValidationResult {
  const errors: Record<string, string> = {};
  const emailError = validateEmail(email);
  if (emailError) {
    errors.email = emailError;
  }
  return { valid: Object.keys(errors).length === 0, errors };
}

export interface ResetPasswordFormValues {
  email: string;
  token: string;
  nueva_contrasena: string;
  confirmarContrasena: string;
}

export function validateResetPasswordForm(values: ResetPasswordFormValues): ValidationResult {
  const errors: Record<string, string> = {};

  const emailError = validateEmail(values.email);
  if (emailError) {
    errors.email = emailError;
  }

  if (!values.token.trim()) {
    errors.token = 'El código es obligatorio';
  } else if (!/^\d{6}$/.test(values.token.trim())) {
    errors.token = 'El código debe tener 6 números';
  }

  const passwordError = validatePasswordField(values.nueva_contrasena);
  if (passwordError) {
    errors.nueva_contrasena = passwordError;
  }

  if (values.nueva_contrasena !== values.confirmarContrasena) {
    errors.confirmarContrasena = 'Las contraseñas no coinciden';
  }

  return { valid: Object.keys(errors).length === 0, errors };
}
