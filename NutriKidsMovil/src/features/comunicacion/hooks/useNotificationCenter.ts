import { useCallback, useEffect } from 'react';

import { useChildSessionStore } from '@features/nino/store/childSessionStore';

import {
  campaignService,
  familyMessageService,
  notificationCenterService,
} from '../services/communicationServices';
import { useCommunicationStore } from '../store/communicationStore';
import type { NotificationCategory } from '../types/communication.types';

export function useNotificationCenter() {
  const activeChild = useChildSessionStore((s) => s.activeChild);
  const notifications = useCommunicationStore((s) => s.notifications);
  const unreadCount = useCommunicationStore((s) => s.unreadCount);
  const loading = useCommunicationStore((s) => s.loading);
  const error = useCommunicationStore((s) => s.error);
  const activeFilter = useCommunicationStore((s) => s.activeFilter);
  const setNotifications = useCommunicationStore((s) => s.setNotifications);
  const setUnreadCount = useCommunicationStore((s) => s.setUnreadCount);
  const setLoading = useCommunicationStore((s) => s.setLoading);
  const setError = useCommunicationStore((s) => s.setError);
  const setActiveFilter = useCommunicationStore((s) => s.setActiveFilter);

  const load = useCallback(async () => {
    if (!activeChild) {
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const filter = activeFilter === 'all' ? undefined : { category: activeFilter as NotificationCategory };
      const [list, count] = await Promise.all([
        notificationCenterService.list(activeChild.ninoId, filter),
        notificationCenterService.unreadCount(activeChild.ninoId),
      ]);
      setNotifications(list);
      setUnreadCount(count);
    } catch {
      setError('No pudimos cargar tus notificaciones');
    } finally {
      setLoading(false);
    }
  }, [activeChild, activeFilter, setError, setLoading, setNotifications, setUnreadCount]);

  useEffect(() => {
    void load();
  }, [load]);

  const markRead = useCallback(
    async (notificationId: string) => {
      if (!activeChild) {
        return;
      }
      await notificationCenterService.markRead(activeChild.ninoId, notificationId);
      await load();
    },
    [activeChild, load],
  );

  const markAllRead = useCallback(async () => {
    if (!activeChild) {
      return;
    }
    await notificationCenterService.markAllRead(activeChild.ninoId);
    await load();
  }, [activeChild, load]);

  return {
    notifications,
    unreadCount,
    loading,
    error,
    activeFilter,
    setActiveFilter,
    markRead,
    markAllRead,
    refresh: load,
  };
}

export function useChildMessages() {
  const activeChild = useChildSessionStore((s) => s.activeChild);
  const messages = useCommunicationStore((s) => s.messages);
  const setMessages = useCommunicationStore((s) => s.setMessages);
  const loading = useCommunicationStore((s) => s.loading);
  const setLoading = useCommunicationStore((s) => s.setLoading);

  const load = useCallback(async () => {
    if (!activeChild) {
      return;
    }
    setLoading(true);
    try {
      const list = await familyMessageService.listForChild(activeChild.ninoId);
      setMessages(list);
    } finally {
      setLoading(false);
    }
  }, [activeChild, setLoading, setMessages]);

  useEffect(() => {
    void load();
  }, [load]);

  const markRead = useCallback(
    async (messageId: string) => {
      if (!activeChild) {
        return;
      }
      await familyMessageService.markRead(activeChild.ninoId, messageId);
      await load();
    },
    [activeChild, load],
  );

  const unreadMessages = messages.filter((m) => !m.read);

  return { messages, unreadMessages, loading, markRead, refresh: load };
}

export function useCampaigns() {
  const activeChild = useChildSessionStore((s) => s.activeChild);
  const campaigns = useCommunicationStore((s) => s.campaigns);
  const setCampaigns = useCommunicationStore((s) => s.setCampaigns);

  useEffect(() => {
    if (!activeChild) {
      return;
    }
    void campaignService.listActive(activeChild.ninoId).then(setCampaigns);
  }, [activeChild, setCampaigns]);

  return { campaigns };
}
