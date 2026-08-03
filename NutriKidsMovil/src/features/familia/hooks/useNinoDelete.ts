import { useCallback, useState } from 'react';

import { AppError } from '@core/errors/AppError';
import { getFriendlyErrorMessage } from '@core/errors/friendlyMessages';

import { ninosService } from '../services/ninosService';

export function useNinoDelete(onDeleted?: () => void) {
  const [deleting, setDeleting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const deleteNino = useCallback(
    async (ninoId: number) => {
      setDeleting(true);
      setError(null);
      try {
        await ninosService.delete(ninoId);
        onDeleted?.();
        return true;
      } catch (err) {
        const message =
          err instanceof AppError ? getFriendlyErrorMessage(err) : 'No pudimos eliminar el perfil. Intenta de nuevo.';
        setError(message);
        return false;
      } finally {
        setDeleting(false);
      }
    },
    [onDeleted],
  );

  return { deleteNino, deleting, error };
}
