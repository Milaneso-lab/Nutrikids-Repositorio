import { STREAK_CONFIG } from '../config/progression.config';
import { registerStreakActivity } from '../domain/calculators/streakCalculator';
import { resolvePetStage } from '../domain/calculators/progressionRules';
import type { ProgressionSnapshot } from '../types/progression.types';
import { progressionEngine } from './progressionEngine';
import type { ProgressionActionSource } from '../types/progression.types';

export const xpService = {
  gain(snapshot: ProgressionSnapshot, amount: number, source: ProgressionActionSource, companionEmoji: string) {
    return progressionEngine.gainXp(snapshot, amount, source, companionEmoji);
  },
};

export const coinsService = {
  spend(snapshot: ProgressionSnapshot, amount: number, source: string) {
    return progressionEngine.spendCoins(snapshot, amount, source);
  },
};

export const energyService = {
  consume(snapshot: ProgressionSnapshot, amount?: number) {
    return progressionEngine.consumeEnergy(snapshot, amount);
  },
};

export const streakService = {
  registerActivity: registerStreakActivity,
  bonusMultiplier(currentStreak: number): number {
    const bonus = 1 + currentStreak * STREAK_CONFIG.bonusPerDay;
    return Math.min(bonus, STREAK_CONFIG.maxBonusMultiplier);
  },
};

export const missionService = {
  advance(snapshot: ProgressionSnapshot, missionId: string, delta: number, companionEmoji: string) {
    return progressionEngine.advanceMission(snapshot, missionId, delta, companionEmoji);
  },
};

export const badgeService = {
  unlock(snapshot: ProgressionSnapshot, badgeId: string) {
    return progressionEngine.unlockBadge(snapshot, badgeId);
  },
};

export const petService = {
  resolveStage: resolvePetStage,
};

export const inventoryService = {
  list(snapshot: ProgressionSnapshot) {
    return snapshot.inventory;
  },
};

export const achievementService = {
  list(snapshot: ProgressionSnapshot) {
    return snapshot.achievements;
  },
};
