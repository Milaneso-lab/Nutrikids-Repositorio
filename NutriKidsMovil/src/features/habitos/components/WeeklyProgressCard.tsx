import React from 'react';
import { Text } from 'react-native';

import { ExperienceBar } from '@features/nino/components/ExperienceBar';
import { KidCard } from '@features/nino/components/KidCard';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import type { HabitWeeklyStats } from '../types/habits.types';

interface WeeklyProgressCardProps {
  weekly: HabitWeeklyStats;
}

export function WeeklyProgressCard({ weekly }: WeeklyProgressCardProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    card: {
      gap: t.spacing.xs,
    },
    title: {
      fontFamily: t.fonts.bold,
      fontSize: 16,
      color: t.colors.ink,
    },
    value: {
      fontFamily: t.fonts.extraBold,
      fontSize: 24,
      color: t.colors.grape,
    },
    hint: {
      fontFamily: t.fonts.regular,
      fontSize: 11,
      color: t.colors.inkSoft,
      marginTop: 4,
    },
  }));

  const maxDays = 7;

  return (
    <KidCard style={styles.card}>
      <Text style={styles.title}>📅 Esta semana</Text>
      <Text style={styles.value}>{weekly.daysActive} días activos</Text>
      <ExperienceBar
        current={weekly.daysActive}
        max={maxDays}
        label={`${weekly.habitsCompleted} hábitos completados`}
        showValues={false}
      />
      <Text style={styles.hint}>¡Cada día cuenta! No hay prisa 🌈</Text>
    </KidCard>
  );
}
