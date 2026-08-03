import Constants from 'expo-constants';
import * as Device from 'expo-device';

import type { NinoAccesoRequest, NinoAccesoResponse } from '@features/auth/types/childAuth.types';
import type { ChildProfile } from '@features/nino/types/nino.types';
import { mapNinoAccesoToChildProfile } from '@features/nino/services/childProfileService';
import { useChildSessionStore } from '@features/nino/store/childSessionStore';

import { childAuthApi } from './childAuthApi';
import {
  clearChildSessionStorage,
  clearParentSessionStorage,
  computeExpiresAt,
  getChildAccessToken,
  getChildRefreshToken,
  getChildSessionMeta,
  isTokenExpired,
  saveChildSessionMeta,
  saveChildTokens,
} from './sessionStorage';
import { isTransientError, withRetry } from '@shared/utils/retry';

function buildDeviceLabel(): string {
  const model = Device.modelName ?? 'mobile';
  const session = Constants.sessionId ?? 'expo';
  return `${model}-${session}`;
}

function mapResponseToProfile(response: NinoAccesoResponse): ChildProfile {
  return mapNinoAccesoToChildProfile(response);
}

export const childAuthService = {
  async acceso(payload: NinoAccesoRequest): Promise<{ profile: ChildProfile }> {
    const response = await childAuthApi.acceso({
      ...payload,
      codigo_vinculacion: payload.codigo_vinculacion.trim().toUpperCase(),
      dispositivo: payload.dispositivo ?? buildDeviceLabel(),
    });

    if (!response.access_token || !response.refresh_token) {
      throw new Error('Tu papá o mamá debe configurar tu acceso primero');
    }

    const profile = mapResponseToProfile(response);
    await this.persistStandaloneSession(profile, {
      accessToken: response.access_token,
      refreshToken: response.refresh_token,
      expiresIn: response.expires_in ?? 900,
    });
    return { profile };
  },

  async persistStandaloneSession(
    profile: ChildProfile,
    tokens: { accessToken: string; refreshToken: string; expiresIn: number },
  ): Promise<void> {
    await clearParentSessionStorage();
    await saveChildTokens(tokens.accessToken, tokens.refreshToken);
    await saveChildSessionMeta({
      standalone: true,
      accessTokenExpiresAt: computeExpiresAt(tokens.expiresIn),
      ninoId: profile.ninoId,
    });
    await useChildSessionStore.getState().enterChildMode(profile, { standalone: true });
  },

  async restoreStandaloneSession(): Promise<ChildProfile | null> {
    const meta = await getChildSessionMeta();
    if (!meta?.standalone) {
      return null;
    }

    const accessToken = await getChildAccessToken();
    const refreshToken = await getChildRefreshToken();
    if (!accessToken || !refreshToken) {
      await clearChildSessionStorage();
      return null;
    }

    if (isTokenExpired(meta.accessTokenExpiresAt)) {
      try {
        await this.refreshChildAccessToken();
      } catch {
        await clearChildSessionStorage();
        return null;
      }
    }

    try {
      const me = await childAuthApi.me();
      const profile = mapResponseToProfile(me);
      await useChildSessionStore.getState().enterChildMode(profile, { standalone: true });
      return profile;
    } catch {
      await clearChildSessionStorage();
      return null;
    }
  },

  async refreshChildAccessToken(): Promise<string> {
    return withRetry(async () => {
      const refreshToken = await getChildRefreshToken();
      if (!refreshToken) {
        throw new Error('No child refresh token');
      }

      const response = await childAuthApi.refresh(refreshToken);
      await saveChildTokens(response.access_token, response.refresh_token);
      const meta = await getChildSessionMeta();
      if (meta) {
        await saveChildSessionMeta({
          ...meta,
          accessTokenExpiresAt: computeExpiresAt(response.expires_in),
        });
      }
      return response.access_token;
    }, { retries: 3, delayMs: 800 });
  },

  async getValidAccessToken(): Promise<string | null> {
    const meta = await getChildSessionMeta();
    if (!meta?.standalone) {
      return null;
    }

    const accessToken = await getChildAccessToken();
    if (!accessToken) {
      return null;
    }

    if (!isTokenExpired(meta.accessTokenExpiresAt, 120_000)) {
      return accessToken;
    }

    try {
      return await this.refreshChildAccessToken();
    } catch (error) {
      if (isTransientError(error)) {
        return accessToken;
      }
      return null;
    }
  },

  async logout(): Promise<void> {
    const refreshToken = await getChildRefreshToken();
    const accessToken = await getChildAccessToken();
    try {
      if (refreshToken && accessToken) {
        await childAuthApi.logout(refreshToken, accessToken);
      }
    } catch {
      // limpiar localmente igual
    } finally {
      await clearChildSessionStorage();
      await useChildSessionStore.getState().exitChildMode({ standalone: true });
    }
  },

  isStandaloneSession(): Promise<boolean> {
    return getChildSessionMeta().then((meta) => Boolean(meta?.standalone));
  },
};
