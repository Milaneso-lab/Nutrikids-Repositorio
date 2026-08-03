import React from 'react';
import { Pressable, Text, View } from 'react-native';

import { useThemedKidStyles } from '../hooks/useThemedKidStyles';

interface KidHabitCardProps {
  emoji: string;
  title: string;
  points?: number;
  completed?: boolean;
  onToggle?: () => void;
}

export function KidHabitCard({
  emoji,
  title,
  points = 10,
  completed = false,
  onToggle,
}: KidHabitCardProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    card: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: t.spacing.sm,
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.md,
      minHeight: 72,
      ...t.shadow.card,
    },
    completed: {
      backgroundColor: t.colors.surfaceMuted,
      borderWidth: 2,
      borderColor: t.colors.mint,
    },
    pressed: {
      opacity: 0.9,
      transform: [{ scale: 0.98 }],
    },
    emoji: {
      fontSize: 32,
    },
    body: {
      flex: 1,
      gap: 2,
    },
    title: {
      fontFamily: t.fonts.bold,
      fontSize: 15,
      color: t.colors.ink,
    },
    titleDone: {
      textDecorationLine: 'line-through',
      color: t.colors.inkSoft,
    },
    points: {
      fontFamily: t.fonts.semiBold,
      fontSize: 12,
      color: t.colors.grape,
    },
    check: {
      width: 36,
      height: 36,
      borderRadius: 18,
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
      fontSize: 18,
      color: t.colors.ink,
    },
  }));

  return (
    <Pressable
      accessibilityRole="checkbox"
      accessibilityState={{ checked: completed }}
      accessibilityLabel={`${title}, ${points} puntos`}
      onPress={onToggle}
      disabled={!onToggle}
      style={({ pressed }) => [styles.card, completed && styles.completed, pressed && styles.pressed]}
    >
      <Text style={styles.emoji}>{emoji}</Text>
      <View style={styles.body}>
        <Text style={[styles.title, completed && styles.titleDone]}>{title}</Text>
        <Text style={styles.points}>+{points} pts</Text>
      </View>
      <View style={[styles.check, completed && styles.checkDone]}>
        <Text style={styles.checkMark}>{completed ? '✓' : ''}</Text>
      </View>
    </Pressable>
  );
}
