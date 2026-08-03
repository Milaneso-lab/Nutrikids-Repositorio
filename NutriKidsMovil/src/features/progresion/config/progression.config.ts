import type { BadgeRarity, MissionKind, PetEvolutionStage } from '../types/progression.types';

/** XP por nivel — alineado con API FastAPI: nivel = floor(xpTotal/100)+1 */
export const XP_PER_LEVEL = 100;

export const ENERGY_CONFIG = {
  max: 100,
  dailyRegen: 100,
  missionCost: 15,
  habitCost: 5,
} as const;

export const COINS_CONFIG = {
  /** Monedas ganadas por cada punto de XP (motor local hasta columna dedicada en API). */
  xpToCoinsRatio: 0.5,
  dailyBonus: 10,
} as const;

export const STREAK_CONFIG = {
  maxBonusMultiplier: 1.5,
  bonusPerDay: 0.05,
} as const;

export const PET_EVOLUTION_THRESHOLDS: Record<PetEvolutionStage, number> = {
  egg: 0,
  baby: 2,
  kid: 5,
  teen: 10,
  hero: 20,
};

export const BADGE_RARITY_COLORS: Record<BadgeRarity, string> = {
  common: '#94A3B8',
  rare: '#60A5FA',
  epic: '#A78BFA',
  legendary: '#FBBF24',
};

export const DEFAULT_DAILY_MISSIONS: Array<{
  id: string;
  kind: MissionKind;
  title: string;
  emoji: string;
  target: number;
  rewardXp: number;
  rewardCoins: number;
}> = [
  {
    id: 'daily-habits-3',
    kind: 'daily',
    title: 'Completa 3 hábitos',
    emoji: '✅',
    target: 3,
    rewardXp: 30,
    rewardCoins: 15,
  },
  {
    id: 'daily-water',
    kind: 'daily',
    title: 'Bebe agua 4 veces',
    emoji: '💧',
    target: 4,
    rewardXp: 20,
    rewardCoins: 10,
  },
];

export const DEFAULT_WEEKLY_MISSIONS: Array<{
  id: string;
  kind: MissionKind;
  title: string;
  emoji: string;
  target: number;
  rewardXp: number;
  rewardCoins: number;
}> = [
  {
    id: 'weekly-streak-5',
    kind: 'weekly',
    title: 'Racha de 5 días',
    emoji: '🔥',
    target: 5,
    rewardXp: 100,
    rewardCoins: 50,
  },
];

export const SEED_BADGES = [
  { id: 'badge-welcome', name: 'Explorador', emoji: '🌟', rarity: 'common' as BadgeRarity, category: 'inicio' },
  { id: 'badge-water', name: 'Héroe del agua', emoji: '💧', rarity: 'rare' as BadgeRarity, category: 'habitos' },
  { id: 'badge-veggie', name: 'Amigo verde', emoji: '🥦', rarity: 'epic' as BadgeRarity, category: 'nutricion' },
];
