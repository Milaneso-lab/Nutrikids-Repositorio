import { useCallback, useEffect, useState } from 'react';

import { AppError } from '@core/errors/AppError';
import { getFriendlyErrorMessage } from '@core/errors/friendlyMessages';

import { ninosService } from '../services/ninosService';
import type { NinoWithPuntos } from '../types/familia.types';

export function useNinoDetail(ninoId: number) {
  const [nino, setNino] = useState<NinoWithPuntos | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const detail = await ninosService.getDetail(ninoId);
      setNino(detail);
    } catch (err) {
      const message =
        err instanceof AppError ? getFriendlyErrorMessage(err) : 'No pudimos cargar el perfil. Intenta de nuevo.';
      setError(message);
    } finally {
      setLoading(false);
    }
  }, [ninoId]);

  useEffect(() => {
    void load();
  }, [load]);

  return { nino, loading, error, reload: load };
}
