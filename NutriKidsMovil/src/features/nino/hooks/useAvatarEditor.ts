import { useCallback, useState } from 'react';

import type { AvatarConfig } from '@features/familia/types/familia.types';
import { getFriendlyErrorMessage } from '@core/errors/friendlyMessages';

import { childProfileService } from '../services/childProfileService';
import { useChildSessionStore } from '../store/childSessionStore';

export function useAvatarEditor() {
  const activeChild = useChildSessionStore((s) => s.activeChild);
  const updateActiveChild = useChildSessionStore((s) => s.updateActiveChild);

  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const saveAvatar = useCallback(
    async (avatarConfig: AvatarConfig) => {
      if (!activeChild) {
        setError('No hay sesión activa');
        return false;
      }

      setSaving(true);
      setError(null);

      try {
        const updated = await childProfileService.updateAvatar(activeChild.ninoId, avatarConfig, activeChild);
        updateActiveChild(updated);
        return true;
      } catch (err) {
        setError(getFriendlyErrorMessage(err));
        return false;
      } finally {
        setSaving(false);
      }
    },
    [activeChild, updateActiveChild],
  );

  return {
    activeChild,
    saving,
    error,
    saveAvatar,
  };
}
