import { REMINDER_TEMPLATES } from '../config/communication.config';
import { createNotification } from '../domain/factories/createNotification';
import { communicationApi, type ApiAlerta } from './communicationApi';
import type {
  AppNotification,
  Campaign,
  FamilyMessage,
  NotificationCategory,
  NotificationFilter,
  ReminderConfig,
  ReminderKind,
} from '../types/communication.types';

const ALERTA_ID_PREFIX = 'alerta-';
const FAMILY_MESSAGE_TYPE = 'mensaje_familiar';

function mapAlertaToNotification(alerta: ApiAlerta): AppNotification {
  const categoryBySeverity: Record<ApiAlerta['severidad'], NotificationCategory> = {
    info: 'recordatorio',
    advertencia: 'habito',
    critica: 'profesional',
  };
  const emojiBySeverity: Record<ApiAlerta['severidad'], string> = {
    info: 'ℹ️',
    advertencia: '⚠️',
    critica: '🚨',
  };

  return {
    id: `${ALERTA_ID_PREFIX}${alerta.id}`,
    ninoId: alerta.nino_id ?? 0,
    category: alerta.tipo === FAMILY_MESSAGE_TYPE ? 'familiar' : categoryBySeverity[alerta.severidad] ?? 'recordatorio',
    title: alerta.tipo === FAMILY_MESSAGE_TYPE ? 'Mensaje de tu familia' : alerta.tipo,
    body: alerta.mensaje,
    emoji: alerta.tipo === FAMILY_MESSAGE_TYPE ? '💌' : emojiBySeverity[alerta.severidad] ?? '📢',
    read: alerta.atendida,
    priority: alerta.severidad === 'critica' ? 'important' : 'normal',
    createdAt: alerta.created_at ?? new Date().toISOString(),
    readAt: alerta.atendida_en,
    metadata: { alertaId: alerta.id, source: 'postgresql' },
  };
}

function isRemoteNotification(notificationId: string): boolean {
  return notificationId.startsWith(ALERTA_ID_PREFIX);
}

function parseAlertaId(notificationId: string): number | null {
  if (!isRemoteNotification(notificationId)) {
    return null;
  }
  const parsed = Number(notificationId.slice(ALERTA_ID_PREFIX.length));
  return Number.isFinite(parsed) ? parsed : null;
}

function mapAlertaToFamilyMessage(alerta: ApiAlerta, ninoId: number): FamilyMessage {
  return {
    id: `${ALERTA_ID_PREFIX}${alerta.id}`,
    ninoId: alerta.nino_id ?? ninoId,
    senderRole: 'padre',
    senderName: 'Familia',
    content: alerta.mensaje,
    emoji: '💌',
    rewardType: 'mensaje',
    read: alerta.atendida,
    createdAt: alerta.created_at ?? new Date().toISOString(),
    readAt: alerta.atendida_en,
  };
}

function buildDefaultReminders(ninoId: number): ReminderConfig[] {
  return (Object.keys(REMINDER_TEMPLATES) as ReminderKind[]).map((kind) => {
    const template = REMINDER_TEMPLATES[kind];
    return {
      id: `${ninoId}-${kind}`,
      ninoId,
      kind,
      emoji: template.emoji,
      hour: template.defaultHour,
      minute: template.defaultMinute,
      enabled: false,
      message: template.messages[0] ?? '',
    };
  });
}

const scheduledPushIds = new Map<number, Record<string, string>>();

export class CommunicationRepository {
  async getNotifications(ninoId: number, filter?: NotificationFilter): Promise<AppNotification[]> {
    try {
      const alertas = await communicationApi.listAlertas(ninoId);
      let list = alertas.map(mapAlertaToNotification).sort((a, b) => b.createdAt.localeCompare(a.createdAt));

      if (filter?.category) {
        list = list.filter((n) => n.category === filter.category);
      }
      if (filter?.unreadOnly) {
        list = list.filter((n) => !n.read);
      }
      return list;
    } catch {
      return [];
    }
  }

  async addNotification(ninoId: number, notification: AppNotification): Promise<AppNotification> {
    await communicationApi.createAlerta({
      nino_id: ninoId,
      tipo: notification.category,
      severidad: notification.priority === 'important' ? 'critica' : 'info',
      mensaje: `${notification.emoji} ${notification.title}: ${notification.body}`,
    });
    return notification;
  }

  async markNotificationRead(_ninoId: number, notificationId: string): Promise<void> {
    const alertaId = parseAlertaId(notificationId);
    if (alertaId !== null) {
      await communicationApi.atenderAlerta(alertaId);
    }
  }

  async markAllNotificationsRead(ninoId: number): Promise<void> {
    try {
      const unread = await communicationApi.listAlertas(ninoId, false);
      await Promise.all(unread.filter((a) => !a.atendida).map((a) => communicationApi.atenderAlerta(a.id)));
    } catch {
      // Ignorar fallos puntuales
    }
  }

  async getMessages(ninoId: number): Promise<FamilyMessage[]> {
    try {
      const alertas = await communicationApi.listAlertas(ninoId);
      return alertas
        .filter((a) => a.tipo === FAMILY_MESSAGE_TYPE)
        .map((a) => mapAlertaToFamilyMessage(a, ninoId))
        .sort((a, b) => b.createdAt.localeCompare(a.createdAt));
    } catch {
      return [];
    }
  }

  async addMessage(ninoId: number, message: FamilyMessage): Promise<FamilyMessage> {
    await communicationApi.createAlerta({
      nino_id: ninoId,
      tipo: FAMILY_MESSAGE_TYPE,
      severidad: 'info',
      mensaje: `${message.emoji} ${message.content}`,
    });
    return message;
  }

  async markMessageRead(ninoId: number, messageId: string): Promise<void> {
    await this.markNotificationRead(ninoId, messageId);
  }

  async getReminders(ninoId: number): Promise<ReminderConfig[]> {
    return buildDefaultReminders(ninoId);
  }

  async updateReminder(_ninoId: number, _reminder: ReminderConfig): Promise<void> {
    // La configuración de recordatorios se gestiona en el dispositivo hasta tener endpoint dedicado.
  }

  async getCampaigns(_ninoId: number): Promise<Campaign[]> {
    return [];
  }

  async saveScheduledPushId(ninoId: number, reminderId: string, pushId: string): Promise<void> {
    const current = scheduledPushIds.get(ninoId) ?? {};
    scheduledPushIds.set(ninoId, { ...current, [reminderId]: pushId });
  }

  async getScheduledPushIds(ninoId: number): Promise<Record<string, string>> {
    return scheduledPushIds.get(ninoId) ?? {};
  }

  createFamilyMessage(params: {
    ninoId: number;
    senderName: string;
    content: string;
    emoji: string;
    rewardType?: FamilyMessage['rewardType'];
  }): FamilyMessage {
    return {
      id: `pending-${Date.now()}`,
      ninoId: params.ninoId,
      senderRole: 'padre',
      senderName: params.senderName,
      content: params.content,
      emoji: params.emoji,
      rewardType: params.rewardType ?? 'mensaje',
      read: false,
      createdAt: new Date().toISOString(),
      readAt: null,
    };
  }
}

export const communicationRepository = new CommunicationRepository();
