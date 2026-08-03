import { SECURE_KEYS, STORAGE_KEYS } from '@core/config/constants';
import { localStorage } from '@core/storage/localStorage';
import { secureTokenStorage } from '@core/storage/secureStorage';

import type { ChildSessionMeta } from '@features/auth/types/childAuth.types';
import type { StoredSession } from '@features/auth/types/auth.types';

export async function saveTokens(accessToken: string, refreshToken: string): Promise<void> {
  await Promise.all([
    secureTokenStorage.set(SECURE_KEYS.accessToken, accessToken),
    secureTokenStorage.set(SECURE_KEYS.refreshToken, refreshToken),
  ]);
}

export async function saveChildTokens(accessToken: string, refreshToken: string): Promise<void> {
  await Promise.all([
    secureTokenStorage.set(SECURE_KEYS.childAccessToken, accessToken),
    secureTokenStorage.set(SECURE_KEYS.childRefreshToken, refreshToken),
  ]);
}

export async function getAccessToken(): Promise<string | null> {
  return secureTokenStorage.get(SECURE_KEYS.accessToken);
}

export async function getChildAccessToken(): Promise<string | null> {
  return secureTokenStorage.get(SECURE_KEYS.childAccessToken);
}

export async function getRefreshToken(): Promise<string | null> {
  return secureTokenStorage.get(SECURE_KEYS.refreshToken);
}

export async function getChildRefreshToken(): Promise<string | null> {
  return secureTokenStorage.get(SECURE_KEYS.childRefreshToken);
}

export async function clearTokens(): Promise<void> {
  await Promise.all([
    secureTokenStorage.remove(SECURE_KEYS.accessToken),
    secureTokenStorage.remove(SECURE_KEYS.refreshToken),
    secureTokenStorage.remove(SECURE_KEYS.childAccessToken),
    secureTokenStorage.remove(SECURE_KEYS.childRefreshToken),
    secureTokenStorage.remove(SECURE_KEYS.childSession),
  ]);
}

export async function saveChildSessionMeta(meta: ChildSessionMeta): Promise<void> {
  await localStorage.setJson(STORAGE_KEYS.childSessionMeta, meta);
}

export async function getChildSessionMeta(): Promise<ChildSessionMeta | null> {
  return localStorage.getJson<ChildSessionMeta>(STORAGE_KEYS.childSessionMeta);
}

export async function clearChildSessionStorage(): Promise<void> {
  await Promise.all([
    secureTokenStorage.remove(SECURE_KEYS.childAccessToken),
    secureTokenStorage.remove(SECURE_KEYS.childRefreshToken),
    localStorage.removeItem(STORAGE_KEYS.childSessionMeta),
    localStorage.removeItem('@nutrikids/active_child_session'),
  ]);
}

export async function saveSession(session: StoredSession): Promise<void> {
  await localStorage.setJson(STORAGE_KEYS.userSession, session);
}

export async function getStoredSession(): Promise<StoredSession | null> {
  return localStorage.getJson<StoredSession>(STORAGE_KEYS.userSession);
}

export async function clearSessionStorage(): Promise<void> {
  await Promise.all([clearTokens(), localStorage.removeItem(STORAGE_KEYS.userSession)]);
}

export async function clearParentSessionStorage(): Promise<void> {
  await Promise.all([
    secureTokenStorage.remove(SECURE_KEYS.accessToken),
    secureTokenStorage.remove(SECURE_KEYS.refreshToken),
    localStorage.removeItem(STORAGE_KEYS.userSession),
  ]);
}

export async function hasOnboardingCompleted(): Promise<boolean> {
  const value = await localStorage.getItem(STORAGE_KEYS.onboardingCompleted);
  return value === 'true';
}

export async function markOnboardingCompleted(): Promise<void> {
  await localStorage.setItem(STORAGE_KEYS.onboardingCompleted, 'true');
}

export function computeExpiresAt(expiresInSeconds: number): number {
  return Date.now() + expiresInSeconds * 1000;
}

export function isTokenExpired(expiresAt: number, bufferMs = 60_000): boolean {
  return Date.now() >= expiresAt - bufferMs;
}
