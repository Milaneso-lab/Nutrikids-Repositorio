import type { ProgressionSnapshot } from './progression.types';

export type ProgressionEventType =
  | 'PROGRESSION_INITIALIZED'
  | 'PROGRESSION_SYNCED'
  | 'XP_GAINED'
  | 'XP_LOST'
  | 'LEVEL_UP'
  | 'COINS_EARNED'
  | 'COINS_SPENT'
  | 'ENERGY_CONSUMED'
  | 'ENERGY_REGENERATED'
  | 'STREAK_UPDATED'
  | 'ACHIEVEMENT_UNLOCKED'
  | 'BADGE_UNLOCKED'
  | 'MISSION_COMPLETED'
  | 'MISSION_PROGRESS'
  | 'INVENTORY_ITEM_ADDED'
  | 'PET_EVOLVED'
  | 'PET_MOOD_CHANGED'
  | 'DAILY_GOAL_COMPLETED';

export interface ProgressionEventMap {
  PROGRESSION_INITIALIZED: { ninoId: number; snapshot: ProgressionSnapshot };
  PROGRESSION_SYNCED: { ninoId: number; snapshot: ProgressionSnapshot };
  XP_GAINED: { amount: number; source: string; snapshot: ProgressionSnapshot };
  XP_LOST: { amount: number; source: string; snapshot: ProgressionSnapshot };
  LEVEL_UP: { previousLevel: number; newLevel: number; snapshot: ProgressionSnapshot };
  COINS_EARNED: { amount: number; source: string; snapshot: ProgressionSnapshot };
  COINS_SPENT: { amount: number; source: string; snapshot: ProgressionSnapshot };
  ENERGY_CONSUMED: { amount: number; snapshot: ProgressionSnapshot };
  ENERGY_REGENERATED: { amount: number; snapshot: ProgressionSnapshot };
  STREAK_UPDATED: { current: number; best: number; snapshot: ProgressionSnapshot };
  ACHIEVEMENT_UNLOCKED: { achievementId: string; snapshot: ProgressionSnapshot };
  BADGE_UNLOCKED: { badgeId: string; snapshot: ProgressionSnapshot };
  MISSION_COMPLETED: { missionId: string; kind: string; snapshot: ProgressionSnapshot };
  MISSION_PROGRESS: { missionId: string; progress: number; snapshot: ProgressionSnapshot };
  INVENTORY_ITEM_ADDED: { itemId: string; snapshot: ProgressionSnapshot };
  PET_EVOLVED: { stage: string; snapshot: ProgressionSnapshot };
  PET_MOOD_CHANGED: { mood: string; snapshot: ProgressionSnapshot };
  DAILY_GOAL_COMPLETED: { snapshot: ProgressionSnapshot };
}

export type ProgressionEventPayload<T extends ProgressionEventType> = ProgressionEventMap[T];

export type ProgressionEventHandler<T extends ProgressionEventType> = (
  payload: ProgressionEventPayload<T>,
) => void;

export interface CelebrationQueueItem {
  id: string;
  type: 'level_up' | 'xp_gain' | 'coins_gain' | 'mission_complete' | 'badge_unlock';
  title: string;
  subtitle?: string;
  emoji: string;
  amount?: number;
}
