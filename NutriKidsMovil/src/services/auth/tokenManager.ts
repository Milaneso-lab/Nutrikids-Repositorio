import { authApi } from './authApi';
import { childAuthService } from './childAuthService';
import {
  computeExpiresAt,
  getAccessToken,
  getChildSessionMeta,
  getRefreshToken,
  getStoredSession,
  isTokenExpired,
  saveSession,
  saveTokens,
} from './sessionStorage';
import { isTransientError, withRetry } from '@shared/utils/retry';
import { useAppStore } from '@state/stores/appStore';

/** Renovar el access token ~2 min antes de expirar para evitar ráfagas de 401. */
const TOKEN_REFRESH_BUFFER_MS = 120_000;

const REFRESH_RETRY_OPTIONS = { retries: 3, delayMs: 800, backoff: 1.6 } as const;

let refreshPromise: Promise<string> | null = null;

function shouldUseChildTokens(): boolean {
  const { sessionPhase } = useAppStore.getState();
  return sessionPhase === 'child';
}

async function refreshLiveAccessToken(): Promise<string> {
  return withRetry(async () => {
    const refreshToken = await getRefreshToken();
    if (!refreshToken) {
      throw new Error('No refresh token available');
    }

    const response = await authApi.refresh(refreshToken);
    await saveTokens(response.access_token, response.refresh_token);

    const session = await getStoredSession();
    if (session) {
      await saveSession({
        ...session,
        accessTokenExpiresAt: computeExpiresAt(response.expires_in),
      });
    }

    return response.access_token;
  }, REFRESH_RETRY_OPTIONS);
}

export async function refreshAccessToken(): Promise<string> {
  if (refreshPromise) {
    return refreshPromise;
  }

  refreshPromise = (async () => {
    const childMeta = await getChildSessionMeta();
    if (childMeta?.standalone && shouldUseChildTokens()) {
      return childAuthService.refreshChildAccessToken();
    }

    return refreshLiveAccessToken();
  })();

  try {
    return await refreshPromise;
  } finally {
    refreshPromise = null;
  }
}

export async function getValidAccessToken(): Promise<string | null> {
  const childMeta = await getChildSessionMeta();
  if (childMeta?.standalone && shouldUseChildTokens()) {
    return childAuthService.getValidAccessToken();
  }

  const accessToken = await getAccessToken();
  const session = await getStoredSession();

  if (!accessToken || !session) {
    return null;
  }

  if (!isTokenExpired(session.accessTokenExpiresAt, TOKEN_REFRESH_BUFFER_MS)) {
    return accessToken;
  }

  try {
    return await refreshAccessToken();
  } catch (error) {
    if (isTransientError(error)) {
      return accessToken;
    }
    return null;
  }
}
