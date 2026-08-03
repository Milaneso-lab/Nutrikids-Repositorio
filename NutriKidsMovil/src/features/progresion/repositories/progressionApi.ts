import { apiGet } from '@core/api/client';
import type { PaginatedResponse } from '@core/api/types';

export interface ApiNinoPuntos {
  nino_id: number;
  puntos_totales: number;
  nivel_actual: number;
  actualizado_en: string | null;
}

export interface ApiNinoLogro {
  id: number;
  nino_id: number;
  logro_id: number;
  obtenido_en: string | null;
}

export interface ApiLogroCatalogo {
  id: number;
  nombre: string;
  descripcion: string | null;
  icono: string | null;
}

export interface ApiNinoReto {
  id: number;
  nino_id: number;
  reto_id: number;
  progreso: Record<string, unknown> | null;
  completado: boolean;
  completado_en: string | null;
}

export const progressionApi = {
  getPuntos(ninoId: number): Promise<ApiNinoPuntos> {
    return apiGet<ApiNinoPuntos>(`/ninos/${ninoId}/puntos`);
  },

  getLogros(ninoId: number): Promise<PaginatedResponse<ApiNinoLogro>> {
    return apiGet<PaginatedResponse<ApiNinoLogro>>(`/ninos/${ninoId}/logros`, {
      params: { page: 1, per_page: 50 },
    });
  },

  getLogrosCatalogo(): Promise<PaginatedResponse<ApiLogroCatalogo>> {
    return apiGet<PaginatedResponse<ApiLogroCatalogo>>('/logros-catalogo', {
      params: { page: 1, per_page: 100 },
    });
  },

  getRetos(ninoId: number): Promise<PaginatedResponse<ApiNinoReto>> {
    return apiGet<PaginatedResponse<ApiNinoReto>>(`/ninos/${ninoId}/retos`, {
      params: { page: 1, per_page: 50 },
    });
  },
};
