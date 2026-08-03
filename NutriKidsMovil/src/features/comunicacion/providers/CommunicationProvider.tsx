import React, { useEffect } from 'react';

import { useChildSessionStore } from '@features/nino/store/childSessionStore';
import { useAppStore } from '@state/stores/appStore';

import { communicationEventBridge } from '../services/communicationEventBridge';
import { reminderService } from '../services/reminderService';
import { notificationCenterService } from '../services/communicationServices';
import { useCommunicationStore } from '../store/communicationStore';

interface CommunicationProviderProps {
  children: React.ReactNode;
}

export function CommunicationProvider({ children }: CommunicationProviderProps): React.JSX.Element {
  const activeChild = useChildSessionStore((s) => s.activeChild);
  const isStandaloneChild = useChildSessionStore((s) => s.isStandalone);
  const user = useAppStore((s) => s.user);
  const setUnreadCount = useCommunicationStore((s) => s.setUnreadCount);

  useEffect(() => {
    const getNinoId = () => activeChild?.ninoId ?? null;
    const getUsuarioId = () => user?.idUsuario ?? null;
    const canUseParentAlertsApi = Boolean(activeChild) && !isStandaloneChild;

    const unsubProgression = communicationEventBridge.subscribeProgressionEvents(getNinoId);

    const unsubPush = communicationEventBridge.onPushReceived(() => {
      if (canUseParentAlertsApi && activeChild) {
        void notificationCenterService.unreadCount(activeChild.ninoId).then(setUnreadCount);
      }
    });
    void communicationEventBridge.initializePush(getUsuarioId, getNinoId).catch(() => undefined);

    if (activeChild) {
      void reminderService.scheduleAllEnabled(activeChild.ninoId).catch(() => undefined);
      if (canUseParentAlertsApi) {
        void notificationCenterService.unreadCount(activeChild.ninoId).then(setUnreadCount);
      }
    }

    return () => {
      unsubProgression();
      unsubPush();
    };
  }, [activeChild, isStandaloneChild, user, setUnreadCount]);

  return <>{children}</>;
}
