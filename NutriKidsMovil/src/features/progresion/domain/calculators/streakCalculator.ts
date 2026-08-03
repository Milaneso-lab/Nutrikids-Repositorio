import { STREAK_CONFIG } from '../../config/progression.config';
import type { StreakState } from '../../types/progression.types';

function todayIsoDate(): string {
  return new Date().toISOString().slice(0, 10);
}

function yesterdayIsoDate(): string {
  const d = new Date();
  d.setDate(d.getDate() - 1);
  return d.toISOString().slice(0, 10);
}

export function calculateStreakBonus(currentStreak: number): number {
  const bonus = 1 + currentStreak * STREAK_CONFIG.bonusPerDay;
  return Math.min(bonus, STREAK_CONFIG.maxBonusMultiplier);
}

export function registerStreakActivity(streak: StreakState): StreakState {
  const today = todayIsoDate();
  const yesterday = yesterdayIsoDate();

  if (streak.lastActiveDate === today) {
    return {
      ...streak,
      bonusMultiplier: calculateStreakBonus(streak.current),
    };
  }

  let current = 1;
  if (streak.lastActiveDate === yesterday) {
    current = streak.current + 1;
  }

  const best = Math.max(streak.best, current);

  return {
    current,
    best,
    lastActiveDate: today,
    bonusMultiplier: calculateStreakBonus(current),
  };
}

export function createInitialStreak(): StreakState {
  return {
    current: 0,
    best: 0,
    lastActiveDate: null,
    bonusMultiplier: 1,
  };
}
