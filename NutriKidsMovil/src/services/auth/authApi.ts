import { rawPost } from '@core/api/rawClient';

import type {
  LoginRequest,
  MessageResponse,
  PasswordForgotRequest,
  PasswordResetRequest,
  RefreshResponse,
  RegisterRequest,
  RegisterResponse,
  TokenPairResponse,
} from '@features/auth/types/auth.types';

const AUTH_PREFIX = '/auth';

export const authApi = {
  login(payload: LoginRequest): Promise<TokenPairResponse> {
    return rawPost<TokenPairResponse>(`${AUTH_PREFIX}/login`, payload);
  },

  register(payload: RegisterRequest): Promise<RegisterResponse> {
    return rawPost<RegisterResponse>(`${AUTH_PREFIX}/register`, payload);
  },

  refresh(refreshToken: string): Promise<RefreshResponse> {
    return rawPost<RefreshResponse>(`${AUTH_PREFIX}/refresh`, { refresh_token: refreshToken });
  },

  logout(refreshToken: string, accessToken: string): Promise<MessageResponse> {
    return rawApiPostWithBearer<MessageResponse>(
      `${AUTH_PREFIX}/logout`,
      { refresh_token: refreshToken },
      accessToken,
    );
  },

  forgotPassword(payload: PasswordForgotRequest): Promise<MessageResponse> {
    return rawPost<MessageResponse>(`${AUTH_PREFIX}/password/forgot`, payload);
  },

  resetPassword(payload: PasswordResetRequest): Promise<MessageResponse> {
    return rawPost<MessageResponse>(`${AUTH_PREFIX}/password/reset`, payload);
  },
};

async function rawApiPostWithBearer<T>(url: string, data: unknown, accessToken: string): Promise<T> {
  const { rawApiClient } = await import('@core/api/rawClient');
  const response = await rawApiClient.post<T>(url, data, {
    headers: { Authorization: `Bearer ${accessToken}` },
  });
  return response.data;
}
