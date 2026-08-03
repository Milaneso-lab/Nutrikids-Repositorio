import Constants from 'expo-constants';
import * as Notifications from 'expo-notifications';
import { Platform } from 'react-native';

import type { PushProvider, ScheduledNotification } from './PushProvider.interface';

let notificationHandlerReady = false;

function ensureNotificationHandler(): void {
  if (notificationHandlerReady) {
    return;
  }
  notificationHandlerReady = true;
  Notifications.setNotificationHandler({
    handleNotification: async () => ({
      shouldShowAlert: true,
      shouldPlaySound: false,
      shouldSetBadge: true,
      shouldShowBanner: true,
      shouldShowList: true,
    }),
  });
}

function resolveExpoProjectId(): string | undefined {
  const extra = Constants.expoConfig?.extra as { eas?: { projectId?: string } } | undefined;
  const fromExtra = extra?.eas?.projectId;
  if (typeof fromExtra === 'string' && fromExtra.length > 0) {
    return fromExtra;
  }
  const envId = process.env.EXPO_PUBLIC_EAS_PROJECT_ID;
  return envId && envId.length > 0 ? envId : undefined;
}

export class ExpoPushProvider implements PushProvider {
  async requestPermissions(): Promise<boolean> {
    ensureNotificationHandler();
    try {
      const { status: existing } = await Notifications.getPermissionsAsync();
      if (existing === 'granted') {
        return true;
      }
      const { status } = await Notifications.requestPermissionsAsync();
      return status === 'granted';
    } catch {
      return false;
    }
  }

  async getDeviceToken(): Promise<string | null> {
    try {
      ensureNotificationHandler();
      const granted = await this.requestPermissions();
      if (!granted) {
        return null;
      }

      const projectId = resolveExpoProjectId();
      if (!projectId) {
        return null;
      }

      if (Platform.OS === 'android') {
        await Notifications.setNotificationChannelAsync('default', {
          name: 'NutriKids',
          importance: Notifications.AndroidImportance.DEFAULT,
          vibrationPattern: [0, 250, 250, 250],
        });
      }

      const tokenData = await Notifications.getExpoPushTokenAsync({ projectId });
      return tokenData.data;
    } catch {
      return null;
    }
  }

  async scheduleLocalNotification(notification: ScheduledNotification): Promise<string> {
    ensureNotificationHandler();
    const id = await Notifications.scheduleNotificationAsync({
      content: {
        title: notification.title,
        body: notification.body,
        data: notification.data ?? {},
        sound: false,
      },
      trigger: {
        type: Notifications.SchedulableTriggerInputTypes.DATE,
        date: notification.triggerDate,
      },
    });
    return id;
  }

  async cancelScheduledNotification(id: string): Promise<void> {
    ensureNotificationHandler();
    await Notifications.cancelScheduledNotificationAsync(id);
  }

  async cancelAllScheduledNotifications(): Promise<void> {
    ensureNotificationHandler();
    await Notifications.cancelAllScheduledNotificationsAsync();
  }

  onNotificationReceived(
    callback: (notification: { title: string; body: string; data?: Record<string, unknown> }) => void,
  ): () => void {
    ensureNotificationHandler();
    const sub = Notifications.addNotificationReceivedListener((event) => {
      callback({
        title: event.request.content.title ?? '',
        body: event.request.content.body ?? '',
        data: (event.request.content.data as Record<string, unknown>) ?? {},
      });
    });
    return () => sub.remove();
  }
}

export const expoPushProvider = new ExpoPushProvider();
