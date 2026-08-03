import type { AvatarConfig } from '@features/familia/types/familia.types';

export interface ChildProfile {
  ninoId: number;
  nombre: string;
  apellidos: string;
  fecha_nacimiento: string;
  avatar_config: AvatarConfig | null;
  nivel: number;
  puntos: number;
  /** Compañero virtual — extensión de avatar_config.companion */
  companion?: string;
}

export interface ChildDailySummary {
  habitsCompleted: number;
  habitsTotal: number;
  streakDays: number;
  xpToday: number;
  xpGoal: number;
  nextChallengeTitle: string;
  nextChallengeEmoji: string;
}

export interface ChildWeeklyProgress {
  daysActive: number;
  daysTotal: number;
}

export type ComingSoonFeature =
  | 'retos'
  | 'logros'
  | 'habitos'
  | 'alimentacion'
  | 'configuracion'
  | 'tienda';

export interface ComingSoonContent {
  emoji: string;
  title: string;
  subtitle: string;
  teaser: string;
}
