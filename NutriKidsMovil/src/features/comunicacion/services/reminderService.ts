import { REMINDER_TEMPLATES } from '../config/communication.config';
import { expoPushProvider } from '../push/ExpoPushProvider';
import { communicationRepository } from '../repositories/communicationRepository';
import { notificationCenterService } from './communicationServices';
import type { ReminderConfig, ReminderKind } from '../types/communication.types';

function nextTriggerDate(hour: number, minute: number): Date {
  const now = new Date();
  const trigger = new Date();
  trigger.setHours(hour, minute, 0, 0);
  if (trigger <= now) {
    trigger.setDate(trigger.getDate() + 1);
  }
  return trigger;
}

function pickMessage(kind: ReminderKind): string {
  const messages = REMINDER_TEMPLATES[kind].messages;
  return messages[Math.floor(Math.random() * messages.length)] ?? messages[0] ?? '¡Hora de cuidarte!';
}

export const reminderService = {
  async list(ninoId: number): Promise<ReminderConfig[]> {
    return communicationRepository.getReminders(ninoId);
  },

  async update(ninoId: number, reminder: ReminderConfig): Promise<void> {
    await communicationRepository.updateReminder(ninoId, reminder);
    if (reminder.enabled) {
      await this.scheduleReminder(ninoId, reminder);
    }
  },

  async scheduleReminder(ninoId: number, reminder: ReminderConfig): Promise<void> {
    const existing = await communicationRepository.getScheduledPushIds(ninoId);
    const oldId = existing[reminder.id];
    if (oldId) {
      await expoPushProvider.cancelScheduledNotification(oldId);
    }

    const message = reminder.message || pickMessage(reminder.kind);
    const pushId = await expoPushProvider.scheduleLocalNotification({
      id: reminder.id,
      title: `${reminder.emoji} Recordatorio amigable`,
      body: message,
      triggerDate: nextTriggerDate(reminder.hour, reminder.minute),
      data: { kind: reminder.kind, ninoId, category: 'recordatorio' },
    });

    await communicationRepository.saveScheduledPushId(ninoId, reminder.id, pushId);

    await notificationCenterService.add(ninoId, {
      category: 'recordatorio',
      title: 'Recordatorio programado',
      body: message,
      emoji: reminder.emoji,
      metadata: { reminderId: reminder.id, scheduled: true },
    });
  },

  async scheduleAllEnabled(ninoId: number): Promise<void> {
    const reminders = await this.list(ninoId);
    for (const reminder of reminders.filter((r) => r.enabled)) {
      await this.scheduleReminder(ninoId, reminder);
    }
  },

  async toggle(ninoId: number, reminderId: string, enabled: boolean): Promise<void> {
    const reminders = await this.list(ninoId);
    const reminder = reminders.find((r) => r.id === reminderId);
    if (!reminder) {
      return;
    }
    await this.update(ninoId, { ...reminder, enabled });
  },
};
