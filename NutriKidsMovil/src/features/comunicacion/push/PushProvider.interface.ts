export interface ScheduledNotification {
  id: string;
  title: string;
  body: string;
  triggerDate: Date;
  data?: Record<string, unknown>;
}

export interface PushProvider {
  requestPermissions(): Promise<boolean>;
  getDeviceToken(): Promise<string | null>;
  scheduleLocalNotification(notification: ScheduledNotification): Promise<string>;
  cancelScheduledNotification(id: string): Promise<void>;
  cancelAllScheduledNotifications(): Promise<void>;
  onNotificationReceived(callback: (notification: { title: string; body: string; data?: Record<string, unknown> }) => void): () => void;
}

export interface RegisterTokenPayload {
  token: string;
  platform: string;
  ninoId?: number;
  usuarioId?: number;
}
