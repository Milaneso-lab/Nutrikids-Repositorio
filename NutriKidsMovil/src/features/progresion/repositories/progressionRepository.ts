import { withRetry } from '@shared/utils/retry';

import { buildXpState } from '../domain/calculators/xpCalculator';
import { getNextLevelUnlocks, resolvePetStage } from '../domain/calculators/progressionRules';
import { createDefaultSnapshot } from '../domain/factories/createDefaultSnapshot';
import type { ProgressionSnapshot } from '../types/progression.types';
import { progressionApi } from './progressionApi';
import type { IProgressionRepository } from './progressionRepository.interface';

export class ProgressionRepository implements IProgressionRepository {
  async load(_ninoId: number): Promise<ProgressionSnapshot | null> {
    return null;
  }

  async save(_snapshot: ProgressionSnapshot): Promise<void> {
    // El progreso se obtiene y persiste en la API (puntos, logros, hábitos).
  }

  async syncFromRemote(ninoId: number, local: ProgressionSnapshot): Promise<ProgressionSnapshot> {
    try {
      const [puntos, logrosPage, catalogPage] = await Promise.all([
        withRetry(() => progressionApi.getPuntos(ninoId)),
        withRetry(() => progressionApi.getLogros(ninoId)),
        withRetry(() => progressionApi.getLogrosCatalogo()),
      ]);

      const catalogMap = new Map(catalogPage.data.map((item) => [item.id, item]));
      const xp = buildXpState(puntos.puntos_totales);

      const achievements = local.achievements.map((achievement) => {
        const remote = logrosPage.data.find((l) => l.logro_id === achievement.catalogId);
        if (!remote) {
          return achievement;
        }
        const catalog = achievement.catalogId ? catalogMap.get(achievement.catalogId) : undefined;
        return {
          ...achievement,
          name: catalog?.nombre ?? achievement.name,
          description: catalog?.descripcion ?? achievement.description,
          emoji: catalog?.icono ?? achievement.emoji,
          unlocked: true,
          unlockedAt: remote.obtenido_en ?? achievement.unlockedAt,
        };
      });

      logrosPage.data.forEach((remote) => {
        if (achievements.some((a) => a.catalogId === remote.logro_id)) {
          return;
        }
        const catalog = catalogMap.get(remote.logro_id);
        achievements.push({
          id: `api-logro-${remote.logro_id}`,
          catalogId: remote.logro_id,
          name: catalog?.nombre ?? `Logro #${remote.logro_id}`,
          description: catalog?.descripcion ?? '',
          emoji: catalog?.icono ?? '🏆',
          category: 'especial',
          unlocked: true,
          unlockedAt: remote.obtenido_en,
          rewardXp: 0,
          rewardCoins: 0,
        });
      });

      return {
        ...local,
        ninoId,
        xp,
        achievements,
        pet: {
          ...local.pet,
          stage: resolvePetStage(xp.currentLevel),
        },
        nextLevelUnlocks: getNextLevelUnlocks(xp.currentLevel),
        lastSyncedAt: new Date().toISOString(),
      };
    } catch {
      return local;
    }
  }
}

export async function loadOrCreateSnapshot(
  ninoId: number,
  companionEmoji: string,
): Promise<ProgressionSnapshot> {
  const repo = new ProgressionRepository();
  const created = createDefaultSnapshot(ninoId, companionEmoji);
  return repo.syncFromRemote(ninoId, created);
}
