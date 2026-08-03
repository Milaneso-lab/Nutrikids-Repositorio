import { useCallback, useEffect } from 'react';

import { useChildSessionStore } from '@features/nino/store/childSessionStore';

import { reminderService } from '../services/reminderService';
import { useCommunicationStore } from '../store/communicationStore';
import type { ReminderConfig } from '../types/communication.types';

export function useReminders() {
  const activeChild = useChildSessionStore((s) => s.activeChild);
  const reminders = useCommunicationStore((s) => s.reminders);
  const setReminders = useCommunicationStore((s) => s.setReminders);
  const loading = useCommunicationStore((s) => s.loading);
  const setLoading = useCommunicationStore((s) => s.setLoading);

  const load = useCallback(async () => {
    if (!activeChild) {
      return;
    }
    setLoading(true);
    try {
      const list = await reminderService.list(activeChild.ninoId);
      setReminders(list);
    } finally {
      setLoading(false);
    }
  }, [activeChild, setLoading, setReminders]);

  useEffect(() => {
    void load();
  }, [load]);

  const toggle = useCallback(
    async (reminderId: string, enabled: boolean) => {
      if (!activeChild) {
        return;
      }
      await reminderService.toggle(activeChild.ninoId, reminderId, enabled);
      await load();
    },
    [activeChild, load],
  );

  const update = useCallback(
    async (reminder: ReminderConfig) => {
      if (!activeChild) {
        return;
      }
      await reminderService.update(activeChild.ninoId, reminder);
      await load();
    },
    [activeChild, load],
  );

  return { reminders, loading, toggle, update, refresh: load };
}

export function usePushNotifications() {
  const requestPermissions = useCallback(async () => {
    const { expoPushProvider } = await import('../push/ExpoPushProvider');
    return expoPushProvider.requestPermissions();
  }, []);

  return { requestPermissions };
}
