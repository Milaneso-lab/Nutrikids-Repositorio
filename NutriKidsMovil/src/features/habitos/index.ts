/** Sistema Inteligente de Hábitos Saludables — NutriKids */
export const HABITOS_FEATURE = 'habitos' as const;

export { habitsService } from './services/habitsService';
export { habitProgressionBridge, resolvePetReaction } from './services/habitProgressionBridge';
export { useHabits, useHabitStatistics } from './hooks/useHabits';
export { useHabitCalendar } from './hooks/useHabitCalendar';
export { useHabitsStore } from './store/habitsStore';

export { HabitsHomeScreen } from './screens/HabitsHomeScreen';
export { HabitCalendarScreen } from './screens/HabitCalendarScreen';
export { HabitStatisticsScreen } from './screens/HabitStatisticsScreen';

export { HabitCard } from './components/HabitCard';
export { DailyHabitTracker } from './components/DailyHabitTracker';
export { ProgressCalendar } from './components/ProgressCalendar';
export { PetReactionCard } from './components/PetReactionCard';
export { RewardAnimation } from './components/RewardAnimation';
export { HealthyActionButton } from './components/HealthyActionButton';
export { WeeklyProgressCard } from './components/WeeklyProgressCard';
export { StatisticsCard } from './components/StatisticsCard';

export type {
  HabitoCatalogo,
  NinoHabito,
  HabitoRegistro,
  HabitDailyProgress,
  HabitStatistics,
  PetReaction,
} from './types/habits.types';
