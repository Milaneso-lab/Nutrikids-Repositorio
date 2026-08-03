/** Sistema Inteligente de Comunicación y Acompañamiento — NutriKids */
export const COMUNICACION_FEATURE = 'comunicacion' as const;

export { CommunicationProvider } from './providers/CommunicationProvider';
export { notificationCenterService, familyMessageService, campaignService } from './services/communicationServices';
export { reminderService } from './services/reminderService';
export { communicationEventBridge } from './services/communicationEventBridge';

export { useNotificationCenter, useChildMessages, useCampaigns } from './hooks/useNotificationCenter';
export { useFamilyMessaging } from './hooks/useFamilyMessaging';
export { useReminders, usePushNotifications } from './hooks/useReminders';
export { useCommunicationStore } from './store/communicationStore';

export { NotificationCenterScreen } from './screens/NotificationCenterScreen';
export { ChildMessagesScreen } from './screens/ChildMessagesScreen';
export { SendFamilyMessageScreen } from './screens/SendFamilyMessageScreen';
export { RemindersSettingsScreen } from './screens/RemindersSettingsScreen';

export { NotificationCard } from './components/NotificationCard';
export { ReminderCard } from './components/ReminderCard';
export { FamilyMessageCard } from './components/FamilyMessageCard';
export { EventCard } from './components/EventCard';
export { RewardMessageCard } from './components/RewardMessageCard';
export { PushNotificationPreview } from './components/PushNotificationPreview';

export type { AppNotification, FamilyMessage, ReminderConfig, Campaign, NotificationCategory } from './types/communication.types';
export type { PushProvider } from './push/PushProvider.interface';
