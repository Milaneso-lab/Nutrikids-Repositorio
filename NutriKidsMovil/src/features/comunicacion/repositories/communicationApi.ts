import { apiGet, apiPost } from '@core/api/client';
import type { PaginatedResponse } from '@core/api/types';
import { withRetry } from '@shared/utils/retry';

import type { RegisterTokenPayload } from '../push/PushProvider.interface';

export interface ApiAlerta {
  id: number;
  nino_id: number | null;
  tipo: string;
  severidad: 'info' | 'advertencia' | 'critica';
  mensaje: string;
  atendida: boolean;
  atendida_por_id: number | null;
  atendida_en: string | null;
  created_at: string | null;
}

export interface CreateAlertaPayload {
  nino_id: number;
  tipo: string;
  severidad: 'info' | 'advertencia' | 'critica';
  mensaje: string;
}

export const communicationApi = {
  async listAlertas(ninoId: number, atendida?: boolean): Promise<ApiAlerta[]> {
    const page = await withRetry(() =>
      apiGet<PaginatedResponse<ApiAlerta>>('/alertas', {
        params: {
          nino_id: ninoId,
          page: 1,
          per_page: 100,
          ...(atendida !== undefined ? { atendida } : {}),
        },
      }),
    );
    return page.data;
  },

  async createAlerta(payload: CreateAlertaPayload): Promise<ApiAlerta> {
    return withRetry(() => apiPost<ApiAlerta>('/alertas', payload));
  },

  async atenderAlerta(alertaId: number, atendidaPorId?: number): Promise<ApiAlerta> {
    return withRetry(() =>
      apiPost<ApiAlerta>(
        `/alertas/${alertaId}/atender`,
        {},
        atendidaPorId ? { params: { atendida_por_id: atendidaPorId } } : undefined,
      ),
    );
  },

  async registerDeviceToken(payload: RegisterTokenPayload): Promise<void> {
    try {
      await apiPost('/dispositivos/registrar-token', {
        token: payload.token,
        platform: payload.platform,
        nino_id: payload.ninoId,
        usuario_id: payload.usuarioId,
      });
    } catch {
      // Endpoint opcional
    }
  },
};
