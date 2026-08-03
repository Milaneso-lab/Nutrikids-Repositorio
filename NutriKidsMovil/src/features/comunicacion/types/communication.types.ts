export type NotificationCategory =
  | 'logro'
  | 'habito'
  | 'reto'
  | 'recompensa'
  | 'recordatorio'
  | 'familiar'
  | 'evento'
  | 'profesional';

export type NotificationPriority = 'normal' | 'important';

export type MessageSenderRole = 'padre' | 'nino' | 'mascota' | 'sistema' | 'nutriologo';

export type ReminderKind = 'hidratacion' | 'alimentacion' | 'actividad' | 'sueno' | 'mision';

export type CampaignKind = 'temporal' | 'semanal' | 'familiar' | 'escolar' | 'especial';

export interface AppNotification {
  id: string;
  ninoId: number;
  category: NotificationCategory;
  title: string;
  body: string;
  emoji: string;
  read: boolean;
  priority: NotificationPriority;
  createdAt: string;
  readAt: string | null;
  metadata?: Record<string, unknown>;
}

export interface FamilyMessage {
  id: string;
  ninoId: number;
  senderRole: MessageSenderRole;
  senderName: string;
  content: string;
  emoji: string;
  rewardType?: 'felicitacion' | 'mensaje' | 'recompensa_virtual';
  read: boolean;
  createdAt: string;
  readAt: string | null;
}

export interface ReminderConfig {
  id: string;
  ninoId: number;
  kind: ReminderKind;
  enabled: boolean;
  hour: number;
  minute: number;
  message: string;
  emoji: string;
}

export interface Campaign {
  id: string;
  kind: CampaignKind;
  title: string;
  description: string;
  emoji: string;
  startDate: string;
  endDate: string;
  active: boolean;
  rewardXp?: number;
  rewardCoins?: number;
}

export interface PushDeviceRegistration {
  token: string;
  platform: 'ios' | 'android' | 'web' | 'unknown';
  registeredAt: string;
}

export interface NotificationFilter {
  category?: NotificationCategory;
  unreadOnly?: boolean;
}
