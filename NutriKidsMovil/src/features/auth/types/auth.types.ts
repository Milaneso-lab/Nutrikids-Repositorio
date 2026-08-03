export interface LoginRequest {
  email: string;
  contrasena: string;
  dispositivo?: string;
  mobile: boolean;
}

export interface RegisterRequest {
  nombre: string;
  apellido_paterno: string;
  apellido_materno?: string;
  email: string;
  contrasena: string;
}

export interface TokenPairResponse {
  access_token: string;
  refresh_token: string;
  token_type: string;
  expires_in: number;
  rol?: string | null;
  id_usuario?: number | null;
  nombre?: string | null;
  apellido_paterno?: string | null;
  email?: string | null;
}

export interface RefreshResponse {
  access_token: string;
  refresh_token: string;
  token_type: string;
  expires_in: number;
}

export interface RegisterResponse {
  id_usuario: number;
  email: string;
  nombre: string;
  rol: string;
  estado: string;
}

export interface MessageResponse {
  message: string;
}

export interface PasswordForgotRequest {
  email: string;
}

export interface PasswordResetRequest {
  email: string;
  token: string;
  nueva_contrasena: string;
}

export interface StoredSession {
  idUsuario: number;
  email: string;
  nombre: string;
  apellidoPaterno?: string;
  rol: string;
  accessTokenExpiresAt: number;
}

export interface AuthUser {
  idUsuario: number;
  email: string;
  nombre: string;
  apellidoPaterno?: string;
  rol: string;
}
