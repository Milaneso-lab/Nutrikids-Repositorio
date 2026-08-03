import { Platform } from 'react-native';

import { createNotification } from '../domain/factories/createNotification';
import { communicationRepository } from '../repositories/communicationRepository';
import type { AppNotification, NotificationCategory, NotificationFilter } from '../types/communication.types';

export const notificationCenterService = {
  async list(ninoId: number, filter?: NotificationFilter): Promise<AppNotification[]> {
    return communicationRepository.getNotifications(ninoId, filter);
  },

  async add(
    ninoId: number,
    params: {
      category: NotificationCategory;
      title: string;
      body: string;
      emoji?: string;
      metadata?: Record<string, unknown>;
    },
  ): Promise<AppNotification> {
    const notification = createNotification({ ninoId, ...params });
    return communicationRepository.addNotification(ninoId, notification);
  },

  async markRead(ninoId: number, notificationId: string): Promise<void> {
    await communicationRepository.markNotificationRead(ninoId, notificationId);
  },

  async markAllRead(ninoId: number): Promise<void> {
    await communicationRepository.markAllNotificationsRead(ninoId);
  },

  async unreadCount(ninoId: number): Promise<number> {
    const list = await communicationRepository.getNotifications(ninoId, { unreadOnly: true });
    return list.length;
  },
};

export const familyMessageService = {
  async sendFromParent(params: {
    ninoId: number;
    parentName: string;
    content: string;
    emoji: string;
    rewardType?: 'felicitacion' | 'mensaje' | 'recompensa_virtual';
  }) {
    const message = communicationRepository.createFamilyMessage({
      ninoId: params.ninoId,
      senderName: params.parentName,
      content: params.content,
      emoji: params.emoji,
      rewardType: params.rewardType,
    });
    return communicationRepository.addMessage(params.ninoId, message);
  },

  async listForChild(ninoId: number) {
    return communicationRepository.getMessages(ninoId);
  },

  async markRead(ninoId: number, messageId: string) {
    await communicationRepository.markMessageRead(ninoId, messageId);
  },
};

export const campaignService = {
  async listActive(ninoId: number) {
    return communicationRepository.getCampaigns(ninoId);
  },
};

export const pushNotificationService = {
  getPlatform(): string {
    return Platform.OS;
  },
};
