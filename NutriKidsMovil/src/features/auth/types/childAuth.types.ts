export interface ChildSessionMeta {
  standalone: boolean;
  accessTokenExpiresAt: number;
  ninoId: number;
}

export interface NinoAccesoRequest {
  codigo_vinculacion: string;
  pin: string;
  confirmar_pin?: string;
  dispositivo?: string;
}

export interface NinoAccesoResponse {
  requiere_configurar_pin: boolean;
  access_token?: string;
  refresh_token?: string;
  token_type?: string;
  expires_in?: number;
  nino_id: number;
  nombre: string;
  apellidos: string;
  fecha_nacimiento: string;
  sexo?: string;
  avatar_config?: Record<string, unknown> | null;
  nivel_actual: number;
  puntos_totales: number;
  companion?: string | null;
}
