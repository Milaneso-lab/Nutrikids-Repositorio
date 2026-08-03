export type SexoNino = 'masculino' | 'femenino' | 'otro';

export interface AvatarConfig {
  emoji?: string;
  backgroundColor?: string;
  /** URI local hasta existir endpoint de subida de fotos. */
  photoUri?: string;
  objetivoNutricional?: string;
  nivelInicial?: number;
  /** Compañero virtual del niño (mascota). */
  companion?: string;
}

export interface Nino {
  id: number;
  padre_id: number;
  nutriologo_asignado_id: number | null;
  nombre: string;
  apellidos: string;
  fecha_nacimiento: string;
  sexo: SexoNino;
  peso_actual_kg: number | null;
  talla_actual_cm: number | null;
  avatar_config: AvatarConfig | null;
  codigo_vinculacion: string | null;
  requiere_vinculacion_padre: boolean;
  created_at: string | null;
  updated_at: string | null;
}

export interface NinoPuntos {
  nino_id: number;
  puntos_totales: number;
  nivel_actual: number;
  actualizado_en: string | null;
}

export interface NinoCreatePayload {
  padre_id: number;
  nutriologo_asignado_id?: number | null;
  nombre: string;
  apellidos: string;
  fecha_nacimiento: string;
  sexo: SexoNino;
  peso_actual_kg?: number | null;
  talla_actual_cm?: number | null;
  avatar_config?: AvatarConfig | null;
}

export interface NinoUpdatePayload {
  nutriologo_asignado_id?: number | null;
  nombre?: string;
  apellidos?: string;
  fecha_nacimiento?: string;
  sexo?: SexoNino;
  peso_actual_kg?: number | null;
  talla_actual_cm?: number | null;
  avatar_config?: AvatarConfig | null;
}

export interface NinoFormValues {
  nombre: string;
  apellidos: string;
  fecha_nacimiento: string;
  sexo: SexoNino | '';
  peso_actual_kg: string;
  talla_actual_cm: string;
  objetivoNutricional: string;
  nivelInicial: string;
  avatar: AvatarConfig;
}

export interface NinoWithPuntos extends Nino {
  puntos?: NinoPuntos | null;
}

export interface FamilySummary {
  totalHijos: number;
  nivelPromedio: number;
  puntosTotales: number;
  ultimaActividad: string | null;
}
