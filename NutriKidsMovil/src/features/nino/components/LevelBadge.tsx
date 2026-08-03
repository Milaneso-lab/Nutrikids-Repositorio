import React from 'react';
import { Text, View } from 'react-native';

import { useThemedKidStyles } from '../hooks/useThemedKidStyles';

interface LevelBadgeProps {
  level: number;
  size?: 'sm' | 'md' | 'lg';
}

export function LevelBadge({ level, size = 'md' }: LevelBadgeProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    badge: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: 4,
      backgroundColor: t.colors.sunshine,
      paddingHorizontal: 12,
      paddingVertical: 6,
      borderRadius: t.radii.pill,
      borderWidth: 2,
      borderColor: t.colors.surface,
      ...t.shadow.button,
    },
    star: {
      fontSize: 14,
    },
    label: {
      fontFamily: t.fonts.bold,
      fontSize: 14,
      color: t.colors.ink,
    },
  }));

  const scale = size === 'sm' ? 0.85 : size === 'lg' ? 1.15 : 1;

  return (
    <View
      style={[styles.badge, { transform: [{ scale }] }]}
      accessibilityLabel={`Nivel ${level}`}
      accessibilityRole="text"
    >
      <Text style={styles.star}>⭐</Text>
      <Text style={styles.label}>Nv. {level}</Text>
    </View>
  );
}
