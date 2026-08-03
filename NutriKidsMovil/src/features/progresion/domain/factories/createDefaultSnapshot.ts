import {
  COINS_CONFIG,
  DEFAULT_DAILY_MISSIONS,
  DEFAULT_WEEKLY_MISSIONS,
  SEED_BADGES,
} from '../../config/progression.config';
import { buildPetState, getNextLevelUnlocks } from '../calculators/progressionRules';
import { createInitialEnergy } from '../calculators/progressionRules';
import { createInitialStreak } from '../calculators/streakCalculator';
import { buildXpState } from '../calculators/xpCalculator';
import type { ProgressionSnapshot } from '../../types/progression.types';

export function createDefaultSnapshot(ninoId: number, companionEmoji: string): ProgressionSnapshot {
  const xp = buildXpState(0);
  const now = new Date().toISOString();

  return {
    ninoId,
    xp,
    coins: {
      balance: COINS_CONFIG.dailyBonus,
      lifetimeEarned: COINS_CONFIG.dailyBonus,
      lifetimeSpent: 0,
      history: [
        {
          id: `coin-${Date.now()}`,
          amount: COINS_CONFIG.dailyBonus,
          type: 'earn',
          source: 'welcome_bonus',
          createdAt: now,
        },
      ],
    },
    energy: createInitialEnergy(),
    streak: createInitialStreak(),
    achievements: [
      {
        id: 'ach-welcome',
        name: '¡Bienvenido aventurero!',
        description: 'Entraste a tu mundo NutriKids',
        emoji: '🎉',
        category: 'especial',
        unlocked: true,
        unlockedAt: now,
        rewardXp: 0,
        rewardCoins: 0,
      },
    ],
    badges: SEED_BADGES.map((badge, index) => ({
      ...badge,
      unlocked: index === 0,
      unlockedAt: index === 0 ? now : null,
    })),
    missions: {
      daily: DEFAULT_DAILY_MISSIONS.map((m) => ({
        ...m,
        progress: 0,
        completed: false,
        expiresAt: endOfDayIso(),
      })),
      weekly: DEFAULT_WEEKLY_MISSIONS.map((m) => ({
        ...m,
        progress: 0,
        completed: false,
        expiresAt: endOfWeekIso(),
      })),
      special: [],
    },
    inventory: [
      {
        id: 'inv-starter-hat',
        itemId: 'hat-star',
        name: 'Gorro estrella',
        emoji: '⭐',
        type: 'accessory',
        quantity: 1,
        equipped: false,
        acquiredAt: now,
      },
    ],
    pet: buildPetState(companionEmoji, xp.currentLevel),
    dailyGoal: {
      title: 'Completa tus hábitos de hoy',
      emoji: '✅',
      progress: 0,
      target: 3,
      completed: false,
    },
    recentUnlocks: [{ type: 'achievement', id: 'ach-welcome', at: now }],
    nextLevelUnlocks: getNextLevelUnlocks(xp.currentLevel),
    lastSyncedAt: null,
  };
}

function endOfDayIso(): string {
  const d = new Date();
  d.setHours(23, 59, 59, 999);
  return d.toISOString();
}

function endOfWeekIso(): string {
  const d = new Date();
  const day = d.getDay();
  const diff = 7 - day;
  d.setDate(d.getDate() + diff);
  d.setHours(23, 59, 59, 999);
  return d.toISOString();
}

export function mergeSnapshotWithApiPuntos(
  snapshot: ProgressionSnapshot,
  puntosTotales: number,
  companionEmoji: string,
): ProgressionSnapshot {
  const xp = buildXpState(puntosTotales);
  return {
    ...snapshot,
    xp,
    pet: buildPetState(companionEmoji, xp.currentLevel, snapshot.pet),
    nextLevelUnlocks: getNextLevelUnlocks(xp.currentLevel),
  };
}
