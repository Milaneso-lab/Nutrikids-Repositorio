import Constants from 'expo-constants';
import * as Device from 'expo-device';

import { sanitizeMessage } from '@core/errors/userMessages';
import { parentAvatarStorage } from '@features/familia/storage/parentAvatarStorage';

import type {
  AuthUser,
  LoginRequest,
  PasswordForgotRequest,
  PasswordResetRequest,
  RegisterRequest,
  StoredSession,
  TokenPairResponse,
} from '@features/auth/types/auth.types';
import { useChildSessionStore } from '@features/nino/store/childSessionStore';
import { useAppStore } from '@state/stores/appStore';

import { authApi } from './authApi';
import {
  clearChildSessionStorage,
  clearSessionStorage,
  computeExpiresAt,
  getAccessToken,
  getRefreshToken,
  getStoredSession,
  hasOnboardingCompleted,
  isTokenExpired,
  markOnboardingCompleted,
  saveSession,
  saveTokens,
} from './sessionStorage';
import { getValidAccessToken, refreshAccessToken } from './tokenManager';

function buildDeviceLabel(): string {
  const model = Device.modelName ?? 'mobile';
  const session = Constants.sessionId ?? 'expo';
  return `${model}-${session}`;
}

function mapTokenResponseToSession(response: TokenPairResponse): StoredSession {
  if (!response.id_usuario || !response.email || !response.nombre || !response.rol) {
    throw new Error(sanitizeMessage(null));
  }

  return {
    idUsuario: response.id_usuario,
    email: response.email,
    nombre: response.nombre,
    apellidoPaterno: response.apellido_paterno ?? undefined,
    rol: response.rol,
    accessTokenExpiresAt: computeExpiresAt(response.expires_in),
  };
}

function mapStoredSessionToUser(session: StoredSession): AuthUser {
  return {
    idUsuario: session.idUsuario,
    email: session.email,
    nombre: session.nombre,
    apellidoPaterno: session.apellidoPaterno,
    rol: session.rol,
  };
}

export const authService = {
  getAccessToken: getValidAccessToken,
  refreshAccessToken,
  hasOnboardingCompleted,
  markOnboardingCompleted,

  async login(email: string, contrasena: string): Promise<AuthUser> {
    await clearChildSessionStorage();
    await useChildSessionStore.getState().exitChildMode({ standalone: true });

    const payload: LoginRequest = {
      email: email.trim().toLowerCase(),
      contrasena,
      dispositivo: buildDeviceLabel(),
      mobile: true,
    };

    const response = await authApi.login(payload);
    await saveTokens(response.access_token, response.refresh_token);
    const session = mapTokenResponseToSession(response);
    await saveSession(session);
    return mapStoredSessionToUser(session);
  },

  async register(data: RegisterRequest): Promise<AuthUser> {
    await authApi.register({
      ...data,
      email: data.email.trim().toLowerCase(),
      apellido_materno: data.apellido_materno?.trim() || undefined,
    });
    return this.login(data.email, data.contrasena);
  },

  async logout(): Promise<void> {
    const refreshToken = await getRefreshToken();
    const accessToken = await getAccessToken();
    try {
      if (refreshToken && accessToken) {
        await authApi.logout(refreshToken, accessToken);
      }
    } catch {
      // Si la API falla, igual limpiamos localmente
    } finally {
      await clearSessionStorage();
      await clearChildSessionStorage();
      await useChildSessionStore.getState().exitChildMode({ standalone: true });
    }
  },

  async forgotPassword(payload: PasswordForgotRequest): Promise<string> {
    const response = await authApi.forgotPassword({
      email: payload.email.trim().toLowerCase(),
    });
    return sanitizeMessage(response.message, 'Si el correo está registrado, te enviamos un código.');
  },

  async resetPassword(payload: PasswordResetRequest): Promise<string> {
    const response = await authApi.resetPassword({
      ...payload,
      email: payload.email.trim().toLowerCase(),
    });
    return sanitizeMessage(response.message, 'Contraseña actualizada correctamente.');
  },

  async restoreSession(): Promise<AuthUser | null> {
    const session = await getStoredSession();
    const refreshToken = await getRefreshToken();

    if (!session || !refreshToken) {
      await clearSessionStorage();
      return null;
    }

    if (isTokenExpired(session.accessTokenExpiresAt)) {
      try {
        await refreshAccessToken();
      } catch {
        await clearSessionStorage();
        return null;
      }
    }

    const updated = await getStoredSession();
    if (!updated) {
      return null;
    }

    const user = mapStoredSessionToUser(updated);
    const avatarConfig = await parentAvatarStorage.get(user.idUsuario);
    useAppStore.getState().setAvatarConfig(avatarConfig);
    return user;
  },

  async clearSession(): Promise<void> {
    await clearSessionStorage();
    await clearChildSessionStorage();
  },
};

export type { AuthUser };
