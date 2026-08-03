import React from 'react';
import { Text, View } from 'react-native';

import { ExperienceBar } from '@features/nino/components/ExperienceBar';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import type { HabitDailyProgress } from '../types/habits.types';

import { HabitCard } from './HabitCard';

interface DailyHabitTrackerProps {
  daily: HabitDailyProgress;
  dailyTarget: number;
  motivationMessage: string;
  togglingId: number | null;
  onToggle: (ninoHabitoId: number) => void;
}

export function DailyHabitTracker({
  daily,
  dailyTarget,
  motivationMessage,
  togglingId,
  onToggle,
}: DailyHabitTrackerProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    wrapper: {
      gap: t.spacing.md,
    },
    header: {
      gap: 4,
    },
    title: {
      fontFamily: t.fonts.extraBold,
      fontSize: 22,
      color: t.colors.textOnGradient,
    },
    subtitle: {
      fontFamily: t.fonts.medium,
      fontSize: 14,
      color: t.colors.textOnGradientMuted,
    },
    list: {
      gap: t.spacing.sm,
    },
    empty: {
      fontFamily: t.fonts.medium,
      fontSize: 14,
      color: t.colors.textOnGradient,
      textAlign: 'center',
      paddingVertical: t.spacing.lg,
    },
  }));

  const progressTarget = Math.max(dailyTarget, daily.total);

  return (
    <View style={styles.wrapper}>
      <View style={styles.header}>
        <Text style={styles.title}>Hábitos de hoy ✅</Text>
        <Text style={styles.subtitle}>{motivationMessage}</Text>
      </View>

      <ExperienceBar
        current={daily.completed}
        max={progressTarget}
        label={`${daily.completed} de ${progressTarget} completados`}
      />

      <View style={styles.list}>
        {daily.entries.map((entry, index) => (
          <HabitCard
            key={entry.ninoHabitoId}
            index={index}
            emoji={entry.emoji}
            title={entry.nombre}
            points={entry.puntosBase}
            categoria={entry.categoria}
            completed={entry.completado}
            loading={togglingId === entry.ninoHabitoId}
            onToggle={() => onToggle(entry.ninoHabitoId)}
          />
        ))}
      </View>

      {daily.entries.length === 0 ? (
        <Text style={styles.empty}>Tus hábitos aparecerán aquí pronto 🌱</Text>
      ) : null}
    </View>
  );
}
