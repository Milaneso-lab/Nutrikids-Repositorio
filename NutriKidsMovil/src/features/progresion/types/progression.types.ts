export type MissionKind = 'daily' | 'weekly' | 'special';
export type BadgeRarity = 'common' | 'rare' | 'epic' | 'legendary';
export type PetEvolutionStage = 'egg' | 'baby' | 'kid' | 'teen' | 'hero';
export type PetMood = 'happy' | 'neutral' | 'sleepy' | 'excited';
export type InventoryItemType = 'accessory' | 'collectible' | 'consumable' | 'pet_item';
export type AchievementCategory = 'habitos' | 'nutricion' | 'retos' | 'social' | 'especial';

export interface XpState {
  total: number;
  currentLevel: number;
  nextLevel: number;
  xpInLevel: number;
  xpToNextLevel: number;
  progress: number;
}

export interface CoinTransaction {
  id: string;
  amount: number;
  type: 'earn' | 'spend';
  source: string;
  createdAt: string;
}

export interface CoinsState {
  balance: number;
  lifetimeEarned: number;
  lifetimeSpent: number;
  history: CoinTransaction[];
}

export interface EnergyState {
  current: number;
  max: number;
  lastRegenDate: string | null;
}

export interface StreakState {
  current: number;
  best: number;
  lastActiveDate: string | null;
  bonusMultiplier: number;
}

export interface AchievementState {
  id: string;
  catalogId?: number;
  name: string;
  description: string;
  emoji: string;
  category: AchievementCategory;
  unlocked: boolean;
  unlockedAt: string | null;
  rewardXp: number;
  rewardCoins: number;
}

export interface BadgeState {
  id: string;
  name: string;
  emoji: string;
  rarity: BadgeRarity;
  category: string;
  unlocked: boolean;
  unlockedAt: string | null;
}

export interface MissionState {
  id: string;
  kind: MissionKind;
  title: string;
  emoji: string;
  target: number;
  progress: number;
  completed: boolean;
  rewardXp: number;
  rewardCoins: number;
  expiresAt: string | null;
}

export interface InventoryItem {
  id: string;
  itemId: string;
  name: string;
  emoji: string;
  type: InventoryItemType;
  quantity: number;
  equipped: boolean;
  acquiredAt: string;
}

export interface PetState {
  companionId: string;
  emoji: string;
  name: string;
  stage: PetEvolutionStage;
  mood: PetMood;
  evolutionProgress: number;
  equippedAccessoryIds: string[];
  xpFed: number;
}

export interface DailyGoalState {
  title: string;
  emoji: string;
  progress: number;
  target: number;
  completed: boolean;
}

export interface LevelUnlockPreview {
  level: number;
  title: string;
  emoji: string;
  description: string;
}

export interface ProgressionSnapshot {
  ninoId: number;
  xp: XpState;
  coins: CoinsState;
  energy: EnergyState;
  streak: StreakState;
  achievements: AchievementState[];
  badges: BadgeState[];
  missions: {
    daily: MissionState[];
    weekly: MissionState[];
    special: MissionState[];
  };
  inventory: InventoryItem[];
  pet: PetState;
  dailyGoal: DailyGoalState;
  recentUnlocks: Array<{ type: 'achievement' | 'badge' | 'level'; id: string; at: string }>;
  nextLevelUnlocks: LevelUnlockPreview[];
  lastSyncedAt: string | null;
}

export interface GainXpResult {
  snapshot: ProgressionSnapshot;
  gained: number;
  leveledUp: boolean;
  previousLevel: number;
  newLevel: number;
}

export interface SpendCoinsResult {
  snapshot: ProgressionSnapshot;
  success: boolean;
  reason?: string;
}

export interface ProgressionActionSource {
  module: string;
  action: string;
  metadata?: Record<string, string | number | boolean>;
}
