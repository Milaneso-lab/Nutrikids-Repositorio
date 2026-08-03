import { useCallback, useEffect, useState } from 'react';

import { AppError } from '@core/errors/AppError';
import { getFriendlyErrorMessage } from '@core/errors/friendlyMessages';

import { childProfileService } from '../services/childProfileService';
import { useChildSessionStore } from '../store/childSessionStore';
import type { ChildProfile } from '../types/nino.types';

interface ChildHomeState {
  child: ChildProfile | null;
  loading: boolean;
  refreshing: boolean;
  error: string | null;
}

export function useChildHome() {
  const activeChild = useChildSessionStore((s) => s.activeChild);
  const updateActiveChild = useChildSessionStore((s) => s.updateActiveChild);

  const [state, setState] = useState<ChildHomeState>({
    child: activeChild,
    loading: !activeChild,
    refreshing: false,
    error: null,
  });

  const load = useCallback(
    async (mode: 'initial' | 'refresh' = 'initial') => {
      if (!activeChild) {
        setState((prev) => ({ ...prev, loading: false, error: 'No hay sesión de niño activa' }));
        return;
      }

      setState((prev) => ({
        ...prev,
        loading: mode === 'initial',
        refreshing: mode === 'refresh',
        error: null,
      }));

      try {
        const profile = await childProfileService.refreshFromApi(activeChild.ninoId);
        updateActiveChild(profile);
        setState({
          child: profile,
          loading: false,
          refreshing: false,
          error: null,
        });
      } catch (err) {
        const message =
          err instanceof AppError ? getFriendlyErrorMessage(err) : 'No pudimos cargar tu aventura. Intenta de nuevo.';
        setState((prev) => ({
          ...prev,
          child: activeChild,
          loading: false,
          refreshing: false,
          error: message,
        }));
      }
    },
    [activeChild, updateActiveChild],
  );

  useEffect(() => {
    void load('initial');
  }, [load]);

  return {
    ...state,
    refresh: () => load('refresh'),
  };
}
