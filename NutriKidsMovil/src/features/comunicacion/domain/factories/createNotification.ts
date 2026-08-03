import { CATEGORY_EMOJI } from '../../config/communication.config';
import type { AppNotification, NotificationCategory, NotificationPriority } from '../../types/communication.types';

function uid(prefix: string): string {
  return `${prefix}-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
}

export function createNotification(params: {
  ninoId: number;
  category: NotificationCategory;
  title: string;
  body: string;
  emoji?: string;
  priority?: NotificationPriority;
  metadata?: Record<string, unknown>;
}): AppNotification {
  return {
    id: uid('notif'),
    ninoId: params.ninoId,
    category: params.category,
    title: params.title,
    body: params.body,
    emoji: params.emoji ?? CATEGORY_EMOJI[params.category] ?? '✨',
    read: false,
    priority: params.priority ?? 'normal',
    createdAt: new Date().toISOString(),
    readAt: null,
    metadata: params.metadata,
  };
}
