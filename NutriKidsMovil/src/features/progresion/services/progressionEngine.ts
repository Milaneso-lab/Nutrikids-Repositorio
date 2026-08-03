import { COINS_CONFIG, ENERGY_CONFIG } from '../config/progression.config';
import { applyXpDelta, buildXpState } from '../domain/calculators/xpCalculator';
import {
  buildPetState,
  consumeEnergy,
  getNextLevelUnlocks,
  regenerateDailyEnergy,
} from '../domain/calculators/progressionRules';
import { registerStreakActivity } from '../domain/calculators/streakCalculator';
import { progressionEventBus } from '../events/progressionEventBus';
import { ProgressionRepository } from '../repositories/progressionRepository';
import type {
  GainXpResult,
  MissionState,
  ProgressionActionSource,
  ProgressionSnapshot,
  SpendCoinsResult,
  BadgeState,
} from '../types/progression.types';
import type { CelebrationQueueItem } from '../types/events.types';

function uid(prefix: string): string {
  return `${prefix}-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
}

export class ProgressionEngine {
  private repository = new ProgressionRepository();

  async initialize(snapshot: ProgressionSnapshot, companionEmoji: string): Promise<ProgressionSnapshot> {
    let current: ProgressionSnapshot = {
      ...snapshot,
      energy: regenerateDailyEnergy(snapshot.energy),
      pet: buildPetState(companionEmoji, snapshot.xp.currentLevel, snapshot.pet),
    };
    const synced = await this.repository.syncFromRemote(current.ninoId, current);
    progressionEventBus.emit('PROGRESSION_INITIALIZED', { ninoId: synced.ninoId, snapshot: synced });
    await this.repository.save(synced);
    return synced;
  }

  async sync(snapshot: ProgressionSnapshot): Promise<ProgressionSnapshot> {
    const synced = await this.repository.syncFromRemote(snapshot.ninoId, snapshot);
    progressionEventBus.emit('PROGRESSION_SYNCED', { ninoId: synced.ninoId, snapshot: synced });
    await this.repository.save(synced);
    return synced;
  }

  gainXp(
    snapshot: ProgressionSnapshot,
    amount: number,
    source: ProgressionActionSource,
    companionEmoji: string,
  ): { result: GainXpResult; celebrations: CelebrationQueueItem[] } {
    const streak = registerStreakActivity(snapshot.streak);
    const bonusAmount = Math.round(amount * streak.bonusMultiplier);
    const previousLevel = snapshot.xp.currentLevel;
    const newTotal = applyXpDelta(snapshot.xp.total, bonusAmount);
    const xp = buildXpState(newTotal);
    const leveledUp = xp.currentLevel > previousLevel;

    const coinsEarned = Math.round(bonusAmount * COINS_CONFIG.xpToCoinsRatio);
    const coinTx = {
      id: uid('coin'),
      amount: coinsEarned,
      type: 'earn' as const,
      source: `${source.module}.${source.action}`,
      createdAt: new Date().toISOString(),
    };

    const celebrations: CelebrationQueueItem[] = [
      {
        id: uid('cel-xp'),
        type: 'xp_gain',
        title: `+${bonusAmount} XP`,
        subtitle: source.action,
        emoji: '⚡',
        amount: bonusAmount,
      },
    ];

    if (coinsEarned > 0) {
      celebrations.push({
        id: uid('cel-coin'),
        type: 'coins_gain',
        title: `+${coinsEarned} monedas`,
        emoji: '🪙',
        amount: coinsEarned,
      });
    }

    if (leveledUp) {
      celebrations.push({
        id: uid('cel-lvl'),
        type: 'level_up',
        title: `¡Nivel ${xp.currentLevel}!`,
        subtitle: '¡Sigue así!',
        emoji: '🎉',
      });
    }

    const updated: ProgressionSnapshot = {
      ...snapshot,
      xp,
      streak,
      coins: {
        balance: snapshot.coins.balance + coinsEarned,
        lifetimeEarned: snapshot.coins.lifetimeEarned + coinsEarned,
        lifetimeSpent: snapshot.coins.lifetimeSpent,
        history: [coinTx, ...snapshot.coins.history].slice(0, 50),
      },
      pet: buildPetState(companionEmoji, xp.currentLevel, snapshot.pet),
      nextLevelUnlocks: getNextLevelUnlocks(xp.currentLevel),
    };

    progressionEventBus.emit('XP_GAINED', { amount: bonusAmount, source: source.action, snapshot: updated });
    if (leveledUp) {
      progressionEventBus.emit('LEVEL_UP', {
        previousLevel,
        newLevel: xp.currentLevel,
        snapshot: updated,
      });
    }
    if (coinsEarned > 0) {
      progressionEventBus.emit('COINS_EARNED', {
        amount: coinsEarned,
        source: source.action,
        snapshot: updated,
      });
    }

    return {
      result: {
        snapshot: updated,
        gained: bonusAmount,
        leveledUp,
        previousLevel,
        newLevel: xp.currentLevel,
      },
      celebrations,
    };
  }

  spendCoins(snapshot: ProgressionSnapshot, amount: number, source: string): SpendCoinsResult {
    if (snapshot.coins.balance < amount) {
      return { snapshot, success: false, reason: 'Monedas insuficientes' };
    }

    const coinTx = {
      id: uid('coin'),
      amount,
      type: 'spend' as const,
      source,
      createdAt: new Date().toISOString(),
    };

    const updated: ProgressionSnapshot = {
      ...snapshot,
      coins: {
        balance: snapshot.coins.balance - amount,
        lifetimeEarned: snapshot.coins.lifetimeEarned,
        lifetimeSpent: snapshot.coins.lifetimeSpent + amount,
        history: [coinTx, ...snapshot.coins.history].slice(0, 50),
      },
    };

    progressionEventBus.emit('COINS_SPENT', { amount, source, snapshot: updated });
    return { snapshot: updated, success: true };
  }

  consumeEnergy(snapshot: ProgressionSnapshot, amount: number = ENERGY_CONFIG.missionCost): {
    snapshot: ProgressionSnapshot;
    success: boolean;
  } {
    const regen = regenerateDailyEnergy(snapshot.energy);
    const { energy, success } = consumeEnergy(regen, amount);
    const updated = { ...snapshot, energy };
    if (success) {
      progressionEventBus.emit('ENERGY_CONSUMED', { amount, snapshot: updated });
    }
    return { snapshot: updated, success };
  }

  advanceMission(
    snapshot: ProgressionSnapshot,
    missionId: string,
    delta: number,
    companionEmoji: string,
  ): {
    snapshot: ProgressionSnapshot;
    completed: boolean;
    celebrations: CelebrationQueueItem[];
  } {
    const celebrations: CelebrationQueueItem[] = [];
    let completedMission = false;

    const patchMissions = (list: MissionState[]) =>
      list.map((mission: MissionState) => {
        if (mission.id !== missionId || mission.completed) {
          return mission;
        }
        const progress = Math.min(mission.target, mission.progress + delta);
        const completed = progress >= mission.target;
        if (completed) {
          completedMission = true;
          celebrations.push({
            id: uid('cel-mission'),
            type: 'mission_complete',
            title: '¡Misión completada!',
            subtitle: mission.title,
            emoji: mission.emoji,
          });
        }
        progressionEventBus.emit('MISSION_PROGRESS', {
          missionId,
          progress,
          snapshot,
        });
        return { ...mission, progress, completed };
      });

    let updated: ProgressionSnapshot = {
      ...snapshot,
      missions: {
        daily: patchMissions(snapshot.missions.daily),
        weekly: patchMissions(snapshot.missions.weekly),
        special: patchMissions(snapshot.missions.special),
      },
    };

    if (completedMission) {
      const mission = [...snapshot.missions.daily, ...snapshot.missions.weekly, ...snapshot.missions.special].find(
        (m) => m.id === missionId,
      );
      if (mission) {
        const { result, celebrations: xpCeleb } = this.gainXp(
          updated,
          mission.rewardXp,
          { module: 'missions', action: mission.id },
          companionEmoji,
        );
        updated = result.snapshot;
        updated.coins.balance += mission.rewardCoins;
        updated.coins.lifetimeEarned += mission.rewardCoins;
        celebrations.push(...xpCeleb);
        progressionEventBus.emit('MISSION_COMPLETED', {
          missionId,
          kind: mission.kind,
          snapshot: updated,
        });
      }
    }

    return { snapshot: updated, completed: completedMission, celebrations };
  }

  unlockBadge(snapshot: ProgressionSnapshot, badgeId: string): {
    snapshot: ProgressionSnapshot;
    unlocked: boolean;
    celebrations: CelebrationQueueItem[];
  } {
    const celebrations: CelebrationQueueItem[] = [];
    let unlocked = false;

    const badges = snapshot.badges.map((badge: BadgeState) => {
      if (badge.id !== badgeId || badge.unlocked) {
        return badge;
      }
      unlocked = true;
      celebrations.push({
        id: uid('cel-badge'),
        type: 'badge_unlock',
        title: '¡Nueva insignia!',
        subtitle: badge.name,
        emoji: badge.emoji,
      });
      return { ...badge, unlocked: true, unlockedAt: new Date().toISOString() };
    });

    const updated = {
      ...snapshot,
      badges,
      recentUnlocks: unlocked
        ? [{ type: 'badge' as const, id: badgeId, at: new Date().toISOString() }, ...snapshot.recentUnlocks].slice(
            0,
            10,
          )
        : snapshot.recentUnlocks,
    };

    if (unlocked) {
      progressionEventBus.emit('BADGE_UNLOCKED', { badgeId, snapshot: updated });
    }

    return { snapshot: updated, unlocked, celebrations };
  }

  updateDailyGoal(
    snapshot: ProgressionSnapshot,
    progress: number,
    companionEmoji: string,
  ): { snapshot: ProgressionSnapshot; celebrations: CelebrationQueueItem[] } {
    const completed = progress >= snapshot.dailyGoal.target;
    const updated: ProgressionSnapshot = {
      ...snapshot,
      dailyGoal: {
        ...snapshot.dailyGoal,
        progress: Math.min(progress, snapshot.dailyGoal.target),
        completed,
      },
    };

    if (completed && !snapshot.dailyGoal.completed) {
      progressionEventBus.emit('DAILY_GOAL_COMPLETED', { snapshot: updated });
      const { result, celebrations } = this.gainXp(
        updated,
        25,
        { module: 'daily_goal', action: 'complete' },
        companionEmoji,
      );
      return { snapshot: result.snapshot, celebrations };
    }

    return { snapshot: updated, celebrations: [] };
  }

  async persist(snapshot: ProgressionSnapshot): Promise<void> {
    await this.repository.save(snapshot);
  }

  /** Efectos locales tras completar un hábito — misiones, objetivo diario, mascota. Sin XP (API o gainXp lo manejan). */
  applyHabitSideEffects(
    snapshot: ProgressionSnapshot,
    companionEmoji: string,
    habitMeta: { nombre: string; icono: string; categoria: string },
  ): { snapshot: ProgressionSnapshot; celebrations: CelebrationQueueItem[] } {
    const celebrations: CelebrationQueueItem[] = [];
    let current = snapshot;

    const missionResult = this.advanceMission(current, 'daily-habits-3', 1, companionEmoji);
    current = missionResult.snapshot;
    celebrations.push(...missionResult.celebrations);

    const isWater =
      habitMeta.nombre.toLowerCase().includes('agua') || habitMeta.icono === 'agua';
    if (isWater) {
      const waterResult = this.advanceMission(current, 'daily-water', 1, companionEmoji);
      current = waterResult.snapshot;
      celebrations.push(...waterResult.celebrations);
    }

    const goalResult = this.updateDailyGoal(current, current.dailyGoal.progress + 1, companionEmoji);
    current = goalResult.snapshot;
    celebrations.push(...goalResult.celebrations);

    current = {
      ...current,
      pet: {
        ...buildPetState(companionEmoji, current.xp.currentLevel, current.pet),
        mood: 'excited',
      },
    };

    progressionEventBus.emit('PET_MOOD_CHANGED', { mood: 'excited', snapshot: current });

    return { snapshot: current, celebrations };
  }

  /** Recompensas locales de monedas cuando XP viene del servidor */
  addCoinsReward(snapshot: ProgressionSnapshot, amount: number, source: string): ProgressionSnapshot {
    if (amount <= 0) {
      return snapshot;
    }
    const coinTx = {
      id: uid('coin'),
      amount,
      type: 'earn' as const,
      source,
      createdAt: new Date().toISOString(),
    };
    const updated = {
      ...snapshot,
      coins: {
        balance: snapshot.coins.balance + amount,
        lifetimeEarned: snapshot.coins.lifetimeEarned + amount,
        lifetimeSpent: snapshot.coins.lifetimeSpent,
        history: [coinTx, ...snapshot.coins.history].slice(0, 50),
      },
    };
    progressionEventBus.emit('COINS_EARNED', { amount, source, snapshot: updated });
    return updated;
  }
}

export const progressionEngine = new ProgressionEngine();
