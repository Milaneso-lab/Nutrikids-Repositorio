import { apiDelete, apiGet, apiPost, apiPut } from '@core/api/client';
import type { ApiMessageResponse, PaginatedResponse } from '@core/api/types';

import type {
  Nino,
  NinoCreatePayload,
  NinoPuntos,
  NinoUpdatePayload,
} from '../types/familia.types';

const NINOS_PREFIX = '/ninos';

export const ninosApi = {
  list(page = 1, perPage = 50): Promise<PaginatedResponse<Nino>> {
    return apiGet<PaginatedResponse<Nino>>(NINOS_PREFIX, {
      params: { page, per_page: perPage },
    });
  },

  getById(ninoId: number): Promise<Nino> {
    return apiGet<Nino>(`${NINOS_PREFIX}/${ninoId}`);
  },

  create(payload: NinoCreatePayload): Promise<Nino> {
    return apiPost<Nino>(NINOS_PREFIX, payload);
  },

  update(ninoId: number, payload: NinoUpdatePayload): Promise<Nino> {
    return apiPut<Nino>(`${NINOS_PREFIX}/${ninoId}`, payload);
  },

  delete(ninoId: number): Promise<ApiMessageResponse> {
    return apiDelete<ApiMessageResponse>(`${NINOS_PREFIX}/${ninoId}`);
  },

  getPuntos(ninoId: number): Promise<NinoPuntos> {
    return apiGet<NinoPuntos>(`${NINOS_PREFIX}/${ninoId}/puntos`);
  },

  vincularDispositivo(
    ninoId: number,
    payload: { pin: string; confirmar_pin: string },
  ): Promise<{ nino_id: number; codigo_vinculacion: string; pin_configurado: boolean }> {
    return apiPost<{ nino_id: number; codigo_vinculacion: string; pin_configurado: boolean }>(
      `${NINOS_PREFIX}/${ninoId}/vincular-dispositivo`,
      payload,
    );
  },
};
