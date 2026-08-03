import { rawPost } from '@core/api/rawClient';
import { apiGet, apiPatch, apiPost } from '@core/api/client';

import type { NinoAccesoRequest, NinoAccesoResponse } from '@features/auth/types/childAuth.types';
import type { RefreshResponse } from '@features/auth/types/auth.types';

export const childAuthApi = {
  acceso(payload: NinoAccesoRequest): Promise<NinoAccesoResponse> {
    return rawPost<NinoAccesoResponse>('/auth/nino/acceso', payload);
  },

  me(): Promise<NinoAccesoResponse> {
    return apiGet<NinoAccesoResponse>('/auth/nino/me');
  },

  updateProfile(payload: {
    nombre?: string;
    apellidos?: string;
    avatar_config?: Record<string, unknown>;
  }): Promise<NinoAccesoResponse> {
    return apiPatch<NinoAccesoResponse>('/auth/nino/me', payload);
  },

  refresh(refreshToken: string): Promise<RefreshResponse> {
    return rawPost<RefreshResponse>('/auth/nino/refresh', { refresh_token: refreshToken });
  },

  logout(refreshToken: string, accessToken: string): Promise<{ message: string }> {
    return apiPost<{ message: string }>(
      '/auth/nino/logout',
      { refresh_token: refreshToken },
      { headers: { Authorization: `Bearer ${accessToken}` } },
    );
  },
};
