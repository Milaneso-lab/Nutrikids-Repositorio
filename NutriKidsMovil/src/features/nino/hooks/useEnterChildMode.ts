import { useCallback, useState } from 'react';

import { mapNinoToChildProfile, useChildSessionStore } from '@features/nino';
import { ninosService } from '@features/familia/services/ninosService';
import type { NinoWithPuntos } from '@features/familia/types/familia.types';

export function useEnterChildMode() {
  const enterChildMode = useChildSessionStore((s) => s.enterChildMode);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const enterWithNino = useCallback(
    async (nino: NinoWithPuntos) => {
      setLoading(true);
      setError(null);
      try {
        const profile = mapNinoToChildProfile(nino);
        await enterChildMode(profile);
        return true;
      } catch {
        setError('No pudimos abrir la experiencia del niño');
        return false;
      } finally {
        setLoading(false);
      }
    },
    [enterChildMode],
  );

  const enterWithNinoId = useCallback(
    async (ninoId: number) => {
      setLoading(true);
      setError(null);
      try {
        const detail = await ninosService.getDetail(ninoId);
        const profile = mapNinoToChildProfile(detail);
        await enterChildMode(profile);
        return true;
      } catch {
        setError('No pudimos abrir la experiencia del niño');
        return false;
      } finally {
        setLoading(false);
      }
    },
    [enterChildMode],
  );

  return { enterWithNino, enterWithNinoId, loading, error };
}
