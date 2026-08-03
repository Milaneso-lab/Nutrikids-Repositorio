import { useCallback, useState } from 'react';

import { parentAvatarStorage } from '@features/familia/storage/parentAvatarStorage';
import { getFriendlyErrorMessage } from '@core/errors/friendlyMessages';
import { authService, type AuthUser } from '@services/auth';
import { useAppStore } from '@state/stores/appStore';

interface AuthActionState {
  loading: boolean;
  error: string | null;
}

export function useAuth() {
  const signIn = useAppStore((state) => state.signIn);
  const signOut = useAppStore((state) => state.signOut);
  const user = useAppStore((state) => state.user);
  const sessionPhase = useAppStore((state) => state.sessionPhase);

  const [state, setState] = useState<AuthActionState>({ loading: false, error: null });

  const runAction = useCallback(async <T>(action: () => Promise<T>): Promise<T | null> => {
    setState({ loading: true, error: null });
    try {
      const result = await action();
      setState({ loading: false, error: null });
      return result;
    } catch (error) {
      const message = getFriendlyErrorMessage(error);
      setState({ loading: false, error: message });
      return null;
    }
  }, []);

  const login = useCallback(
    async (email: string, contrasena: string): Promise<AuthUser | null> => {
      const userResult = await runAction(() => authService.login(email, contrasena));
      if (userResult) {
        const avatarConfig = await parentAvatarStorage.get(userResult.idUsuario);
        signIn(userResult, avatarConfig);
      }
      return userResult;
    },
    [runAction, signIn],
  );

  const register = useCallback(
    async (payload: Parameters<typeof authService.register>[0]): Promise<AuthUser | null> => {
      const userResult = await runAction(() => authService.register(payload));
      if (userResult) {
        const avatarConfig = await parentAvatarStorage.get(userResult.idUsuario);
        signIn(userResult, avatarConfig);
      }
      return userResult;
    },
    [runAction, signIn],
  );

  const logout = useCallback(async (): Promise<void> => {
    setState({ loading: true, error: null });
    try {
      await authService.logout();
    } finally {
      signOut();
      setState({ loading: false, error: null });
    }
  }, [signOut]);

  const forgotPassword = useCallback(
    async (email: string): Promise<string | null> => {
      return runAction(() => authService.forgotPassword({ email }));
    },
    [runAction],
  );

  const resetPassword = useCallback(
    async (payload: Parameters<typeof authService.resetPassword>[0]): Promise<string | null> => {
      return runAction(() => authService.resetPassword(payload));
    },
    [runAction],
  );

  const clearError = useCallback(() => {
    setState((prev) => ({ ...prev, error: null }));
  }, []);

  const isAuthenticated = sessionPhase === 'parent' || sessionPhase === 'child';

  return {
    user,
    sessionPhase,
    isAuthenticated,
    loading: state.loading,
    error: state.error,
    login,
    register,
    logout,
    forgotPassword,
    resetPassword,
    clearError,
  };
}
