import { AGE_HABIT_RECOMMENDATIONS, DEFAULT_DAILY_TARGET } from '../../config/habits.config';

export function getDailyTargetForAge(age: number | null): number {
  if (age == null) {
    return DEFAULT_DAILY_TARGET;
  }

  const match = AGE_HABIT_RECOMMENDATIONS.find((r) => age >= r.minAge && age <= r.maxAge);
  return match?.dailyTarget ?? DEFAULT_DAILY_TARGET;
}

export function getAgeMotivationMessage(age: number | null): string {
  if (age == null) {
    return 'Cada hábito te hace más fuerte y feliz';
  }

  const match = AGE_HABIT_RECOMMENDATIONS.find((r) => age >= r.minAge && age <= r.maxAge);
  return match?.message ?? 'Cada hábito te hace más fuerte y feliz';
}
