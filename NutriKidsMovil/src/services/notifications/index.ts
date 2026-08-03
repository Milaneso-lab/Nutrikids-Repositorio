/**
 * @deprecated Usar @features/comunicacion — este módulo reexporta la implementación actual.
 */
export { expoPushProvider } from '@features/comunicacion/push/ExpoPushProvider';
export { communicationApi } from '@features/comunicacion/repositories/communicationApi';

export const notificationsService = {
  async registerDeviceToken(token: string): Promise<void> {
    const { communicationApi: api } = await import('@features/comunicacion/repositories/communicationApi');
    const { pushNotificationService } = await import('@features/comunicacion/services/communicationServices');
    await api.registerDeviceToken({
      token,
      platform: pushNotificationService.getPlatform(),
    });
  },
  async requestPermissions(): Promise<boolean> {
    const { expoPushProvider: provider } = await import('@features/comunicacion/push/ExpoPushProvider');
    return provider.requestPermissions();
  },
};
