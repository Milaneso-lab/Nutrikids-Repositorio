import { XP_PER_LEVEL } from '../../config/progression.config';
import type { XpState } from '../../types/progression.types';

export function calculateLevelFromTotalXp(totalXp: number): number {
  return Math.max(1, Math.floor(Math.max(0, totalXp) / XP_PER_LEVEL) + 1);
}

export function buildXpState(totalXp: number): XpState {
  const safeTotal = Math.max(0, totalXp);
  const currentLevel = calculateLevelFromTotalXp(safeTotal);
  const xpInLevel = safeTotal % XP_PER_LEVEL;
  const xpToNextLevel = XP_PER_LEVEL;

  return {
    total: safeTotal,
    currentLevel,
    nextLevel: currentLevel + 1,
    xpInLevel,
    xpToNextLevel,
    progress: xpInLevel / xpToNextLevel,
  };
}

export function xpRequiredForLevel(level: number): number {
  return Math.max(0, (level - 1) * XP_PER_LEVEL);
}

export function canLoseXp(): boolean {
  return false;
}

export function applyXpDelta(totalXp: number, delta: number): number {
  const next = totalXp + delta;
  if (canLoseXp()) {
    return Math.max(0, next);
  }
  return Math.max(0, next);
}
