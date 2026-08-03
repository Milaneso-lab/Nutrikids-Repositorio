import { Platform } from 'react-native';

import { progressionEventBus } from '@features/progresion/events/progressionEventBus';
import type { ProgressionEventMap } from '@features/progresion/types/events.types';

import { communicationApi } from '../repositories/communicationApi';
import { expoPushProvider } from '../push/ExpoPushProvider';
import { notificationCenterService } from './communicationServices';

function getNinoIdFromSnapshot(snapshot: { ninoId: number } | undefined): number | null {
  return snapshot?.ninoId ?? null;
}

export const communicationEventBridge = {
  subscribeProgressionEvents(getNinoId: () => number | null): () => void {
    const unsubs: Array<() => void> = [];

    unsubs.push(
      progressionEventBus.on('LEVEL_UP', (payload: ProgressionEventMap['LEVEL_UP']) => {
        const ninoId = getNinoId() ?? getNinoIdFromSnapshot(payload.snapshot);
        if (!ninoId) return;
        void notificationCenterService.add(ninoId, {
          category: 'logro',
          title: `¡Nivel ${payload.newLevel}!`,
          body: '¡Subiste de nivel! Sigue así, campeón',
          emoji: '🎉',
        });
      }),
    );

    unsubs.push(
      progressionEventBus.on('BADGE_UNLOCKED', (payload: ProgressionEventMap['BADGE_UNLOCKED']) => {
        const ninoId = getNinoId() ?? getNinoIdFromSnapshot(payload.snapshot);
        if (!ninoId) return;
        void notificationCenterService.add(ninoId, {
          category: 'logro',
          title: '¡Nueva insignia!',
          body: 'Desbloqueaste una insignia especial',
          emoji: '🏅',
        });
      }),
    );

    unsubs.push(
      progressionEventBus.on('MISSION_COMPLETED', (payload: ProgressionEventMap['MISSION_COMPLETED']) => {
        const ninoId = getNinoId() ?? getNinoIdFromSnapshot(payload.snapshot);
        if (!ninoId) return;
        void notificationCenterService.add(ninoId, {
          category: 'reto',
          title: '¡Misión completada!',
          body: 'Completaste una misión. ¡Qué bien!',
          emoji: '🎯',
        });
      }),
    );

    unsubs.push(
      progressionEventBus.on('DAILY_GOAL_COMPLETED', (payload: ProgressionEventMap['DAILY_GOAL_COMPLETED']) => {
        const ninoId = getNinoId() ?? getNinoIdFromSnapshot(payload.snapshot);
        if (!ninoId) return;
        void notificationCenterService.add(ninoId, {
          category: 'habito',
          title: '¡Objetivo del día logrado!',
          body: 'Completaste tu meta de hoy. ¡Eres genial!',
          emoji: '⭐',
        });
      }),
    );

    unsubs.push(
      progressionEventBus.on('COINS_EARNED', (payload: ProgressionEventMap['COINS_EARNED']) => {
        const ninoId = getNinoId() ?? getNinoIdFromSnapshot(payload.snapshot);
        if (!ninoId || payload.amount < 5) return;
        void notificationCenterService.add(ninoId, {
          category: 'recompensa',
          title: `+${payload.amount} monedas`,
          body: '¡Ganaste monedas por tu esfuerzo!',
          emoji: '🪙',
        });
      }),
    );

    return () => unsubs.forEach((u) => u());
  },

  async initializePush(getUsuarioId: () => number | null, getNinoId: () => number | null): Promise<void> {
    const token = await expoPushProvider.getDeviceToken();
    if (!token) {
      return;
    }
    await communicationApi.registerDeviceToken({
      token,
      platform: Platform.OS,
      ninoId: getNinoId() ?? undefined,
      usuarioId: getUsuarioId() ?? undefined,
    });
  },

  onPushReceived(callback: (title: string, body: string) => void): () => void {
    return expoPushProvider.onNotificationReceived(({ title, body }) => {
      callback(title, body);
    });
  },
};
