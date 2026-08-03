import { useCallback, useRef, useState } from 'react';

import { AppError } from '@core/errors/AppError';
import { getFriendlyErrorMessage } from '@core/errors/friendlyMessages';

import { ninosService } from '../services/ninosService';
import type { FamilySummary, NinoWithPuntos } from '../types/familia.types';

interface UseNinosListState {
  ninos: NinoWithPuntos[];
  summary: FamilySummary;
  loading: boolean;
  refreshing: boolean;
  error: string | null;
  refreshError: string | null;
}

const EMPTY_SUMMARY: FamilySummary = {
  totalHijos: 0,
  nivelPromedio: 0,
  puntosTotales: 0,
  ultimaActividad: null,
};

/** Evita recargar en cada foco si los datos son recientes. */
const STALE_AFTER_MS = 30_000;

export function useNinosList() {
  const [state, setState] = useState<UseNinosListState>({
    ninos: [],
    summary: EMPTY_SUMMARY,
    loading: true,
    refreshing: false,
    error: null,
    refreshError: null,
  });
  const fetchInFlight = useRef(false);
  const lastSuccessAt = useRef(0);

  const fetchList = useCallback(
    async (mode: 'initial' | 'refresh' = 'initial', options?: { force?: boolean }) => {
      if (fetchInFlight.current) {
        return;
      }

      const hasCachedData = state.ninos.length > 0;
      const effectiveMode = mode === 'refresh' && !hasCachedData ? 'initial' : mode;

      if (
        mode === 'refresh' &&
        !options?.force &&
        hasCachedData &&
        Date.now() - lastSuccessAt.current < STALE_AFTER_MS
      ) {
        return;
      }

      fetchInFlight.current = true;
      setState((prev) => ({
        ...prev,
        loading: effectiveMode === 'initial' && prev.ninos.length === 0,
        refreshing: effectiveMode === 'refresh' || (effectiveMode === 'initial' && prev.ninos.length > 0),
        error: effectiveMode === 'initial' && prev.ninos.length === 0 ? null : prev.error,
        refreshError: null,
      }));

      try {
        const result = await ninosService.listWithPuntos();
        lastSuccessAt.current = Date.now();
        setState({
          ninos: result.ninos,
          summary: result.summary,
          loading: false,
          refreshing: false,
          error: null,
          refreshError: null,
        });
      } catch (error) {
        const message =
          error instanceof AppError ? getFriendlyErrorMessage(error) : 'No pudimos cargar los perfiles. Intenta de nuevo.';

        setState((prev) => {
          if (prev.ninos.length > 0) {
            return {
              ...prev,
              loading: false,
              refreshing: false,
              refreshError: message,
            };
          }

          return {
            ...prev,
            loading: false,
            refreshing: false,
            error: message,
          };
        });
      } finally {
        fetchInFlight.current = false;
      }
    },
    [state.ninos.length],
  );

  const refresh = useCallback(() => fetchList('refresh'), [fetchList]);
  const retry = useCallback(() => fetchList('initial', { force: true }), [fetchList]);

  return {
    ...state,
    refresh,
    retry,
  };
};
