import { COINS_CONFIG } from '@features/progresion/config/progression.config';
import { mergeSnapshotWithApiPuntos } from '@features/progresion/domain/factories/createDefaultSnapshot';
import { progressionEngine } from '@features/progresion/services/progressionEngine';
import { progressionApi } from '@features/progresion/repositories/progressionApi';
import type { ProgressionSnapshot } from '@features/progresion/types/progression.types';
import type { CelebrationQueueItem } from '@features/progresion/types/events.types';
import { ninosService } from '@features/familia/services/ninosService';

import type { HabitoCatalogo, NinoHabito } from '../types/habits.types';

export interface HabitCompletionResult {
  snapshot: ProgressionSnapshot;
  celebrations: CelebrationQueueItem[];
  newlyCompleted: boolean;
}

function habitMeta(habito: NinoHabito): { nombre: string; icono: string; categoria: string } {
  return {
    nombre: habito.catalogo?.nombre ?? '',
    icono: habito.catalogo?.icono ?? 'default',
    categoria: habito.catalogo?.categoria ?? 'alimentacion',
  };
}

export const habitProgressionBridge = {
  async onHabitToggled(
    snapshot: ProgressionSnapshot,
    habito: NinoHabito,
    completado: boolean,
    wasCompleted: boolean,
    companionEmoji: string,
  ): Promise<HabitCompletionResult> {
    if (!completado) {
      return { snapshot, celebrations: [], newlyCompleted: false };
    }

    if (wasCompleted) {
      return { snapshot, celebrations: [], newlyCompleted: false };
    }

    const meta = habitMeta(habito);
    const puntosBase = habito.catalogo?.puntosBase ?? 10;
    let celebrations: CelebrationQueueItem[] = [];
    let current = snapshot;

    try {
      const puntos = await ninosService.getPuntos(snapshot.ninoId);
      current = mergeSnapshotWithApiPuntos(current, puntos.puntos_totales, companionEmoji);
      const coins = Math.round(puntosBase * COINS_CONFIG.xpToCoinsRatio);
      current = progressionEngine.addCoinsReward(current, coins, `habitos.${meta.nombre}`);
      if (coins > 0) {
        celebrations.push({
          id: `cel-coin-${Date.now()}`,
          type: 'coins_gain',
          title: `+${coins} monedas`,
          emoji: '🪙',
          amount: coins,
        });
      }
    } catch {
      const { result, celebrations: xpCeleb } = progressionEngine.gainXp(
        current,
        puntosBase,
        { module: 'habitos', action: meta.nombre },
        companionEmoji,
      );
      current = result.snapshot;
      celebrations.push(...xpCeleb);
    }

    const sideEffects = progressionEngine.applyHabitSideEffects(current, companionEmoji, meta);
    current = sideEffects.snapshot;
    celebrations.push(...sideEffects.celebrations);

    celebrations.push({
      id: `cel-habit-${Date.now()}`,
      type: 'xp_gain',
      title: '¡Hábito completado!',
      subtitle: meta.nombre,
      emoji: '🌟',
    });

    await progressionEngine.persist(current);

    return { snapshot: current, celebrations, newlyCompleted: true };
  },

  async syncAfterRegistration(snapshot: ProgressionSnapshot, companionEmoji: string): Promise<ProgressionSnapshot> {
    try {
      const puntos = await progressionApi.getPuntos(snapshot.ninoId);
      return mergeSnapshotWithApiPuntos(snapshot, puntos.puntos_totales, companionEmoji);
    } catch {
      return snapshot;
    }
  },
};

export function resolvePetReaction(
  completedToday: number,
  totalToday: number,
  petEmoji: string,
): { emoji: string; message: string } {
  const ratio = totalToday > 0 ? completedToday / totalToday : 0;

  if (ratio >= 1) {
    return {
      emoji: petEmoji,
      message: '¡Completaste todos tus hábitos! Eres una estrella ⭐',
    };
  }
  if (ratio >= 0.5) {
    return {
      emoji: petEmoji,
      message: '¡Vas muy bien! Cada hábito nos hace más fuertes 💪',
    };
  }
  if (completedToday > 0) {
    return {
      emoji: petEmoji,
      message: '¡Buen comienzo! Sigamos cuidándonos juntos 🌱',
    };
  }
  return {
    emoji: petEmoji,
    message: '¡Hola! Hoy es un gran día para cuidarnos 🌈',
  };
}
