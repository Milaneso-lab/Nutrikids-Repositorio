import React from 'react';
import { ActivityIndicator, Pressable, Text, View } from 'react-native';
import Animated, { FadeInRight } from 'react-native-reanimated';

import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import { HABIT_CATEGORY_COLORS } from '../config/habits.config';
import type { HabitoCategoria } from '../types/habits.types';

export interface HabitCardProps {
  emoji: string;
  title: string;
  description?: string;
  points?: number;
  completed?: boolean;
  categoria?: HabitoCategoria;
  loading?: boolean;
  index?: number;
  onToggle?: () => void;
}

export function HabitCard({
  emoji,
  title,
  description,
  points = 10,
  completed = false,
  categoria = 'alimentacion',
  loading = false,
  index = 0,
  onToggle,
}: HabitCardProps): React.JSX.Element {
  const { colors } = useKidTheme();
  const styles = useThemedKidStyles((t) => ({
    card: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: t.spacing.sm,
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.md,
      minHeight: 80,
      borderLeftWidth: 5,
      ...t.shadow.card,
    },
    completed: {
      backgroundColor: t.colors.surfaceMuted,
    },
    pressed: {
      opacity: 0.92,
      transform: [{ scale: 0.98 }],
    },
    emoji: {
      fontSize: 34,
    },
    body: {
      flex: 1,
      gap: 2,
    },
    title: {
      fontFamily: t.fonts.bold,
      fontSize: 16,
      color: t.colors.ink,
    },
    titleDone: {
      color: t.colors.inkSoft,
    },
    description: {
      fontFamily: t.fonts.regular,
      fontSize: 12,
      color: t.colors.inkSoft,
    },
    points: {
      fontFamily: t.fonts.semiBold,
      fontSize: 12,
      color: t.colors.grape,
      marginTop: 2,
    },
    check: {
      width: 40,
      height: 40,
      borderRadius: 20,
      borderWidth: 2,
      borderColor: t.colors.inputBorder,
      alignItems: 'center',
      justifyContent: 'center',
    },
    checkDone: {
      backgroundColor: t.colors.mint,
      borderColor: t.colors.mint,
    },
    checkMark: {
      fontFamily: t.fonts.bold,
      fontSize: 20,
      color: t.colors.ink,
    },
  }));

  const accent = HABIT_CATEGORY_COLORS[categoria];

  return (
    <Animated.View entering={FadeInRight.delay(index * 50).springify()}>
      <Pressable
        accessibilityRole="checkbox"
        accessibilityState={{ checked: completed, busy: loading }}
        accessibilityLabel={`${title}, ${points} puntos${completed ? ', completado' : ''}`}
        onPress={onToggle}
        disabled={!onToggle || loading}
        style={({ pressed }) => [
          styles.card,
          { borderLeftColor: accent },
          completed && styles.completed,
          pressed && styles.pressed,
        ]}
      >
        <Text style={styles.emoji}>{emoji}</Text>
        <View style={styles.body}>
          <Text style={[styles.title, completed && styles.titleDone]}>{title}</Text>
          {description ? <Text style={styles.description}>{description}</Text> : null}
          <Text style={styles.points}>+{points} pts ✨</Text>
        </View>
        <View style={[styles.check, completed && styles.checkDone]}>
          {loading ? (
            <ActivityIndicator size="small" color={colors.grape} />
          ) : (
            <Text style={styles.checkMark}>{completed ? '✓' : ''}</Text>
          )}
        </View>
      </Pressable>
    </Animated.View>
  );
}
