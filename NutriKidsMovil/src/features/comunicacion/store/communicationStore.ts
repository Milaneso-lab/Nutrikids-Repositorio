import { create } from 'zustand';

import type { AppNotification, Campaign, FamilyMessage, NotificationCategory, ReminderConfig } from '../types/communication.types';

interface CommunicationStoreState {
  notifications: AppNotification[];
  messages: FamilyMessage[];
  reminders: ReminderConfig[];
  campaigns: Campaign[];
  unreadCount: number;
  loading: boolean;
  error: string | null;
  activeFilter: NotificationCategory | 'all';
  setNotifications: (items: AppNotification[]) => void;
  setMessages: (items: FamilyMessage[]) => void;
  setReminders: (items: ReminderConfig[]) => void;
  setCampaigns: (items: Campaign[]) => void;
  setUnreadCount: (count: number) => void;
  setLoading: (loading: boolean) => void;
  setError: (error: string | null) => void;
  setActiveFilter: (filter: NotificationCategory | 'all') => void;
  reset: () => void;
}

export const useCommunicationStore = create<CommunicationStoreState>((set) => ({
  notifications: [],
  messages: [],
  reminders: [],
  campaigns: [],
  unreadCount: 0,
  loading: false,
  error: null,
  activeFilter: 'all',

  setNotifications: (notifications) => set({ notifications }),
  setMessages: (messages) => set({ messages }),
  setReminders: (reminders) => set({ reminders }),
  setCampaigns: (campaigns) => set({ campaigns }),
  setUnreadCount: (unreadCount) => set({ unreadCount }),
  setLoading: (loading) => set({ loading }),
  setError: (error) => set({ error }),
  setActiveFilter: (activeFilter) => set({ activeFilter }),
  reset: () =>
    set({
      notifications: [],
      messages: [],
      reminders: [],
      campaigns: [],
      unreadCount: 0,
      loading: false,
      error: null,
      activeFilter: 'all',
    }),
}));
