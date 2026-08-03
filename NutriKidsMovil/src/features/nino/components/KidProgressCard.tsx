import React from 'react';
import { Pressable, Text, View } from 'react-native';
import Animated, { FadeInDown } from 'react-native-reanimated';

import { useThemedKidStyles } from '../hooks/useThemedKidStyles';

interface KidProgressCardProps {
  emoji: string;
  title: string;
  subtitle: string;
  progress: number;
  index?: number;
  onPress?: () => void;
}

export function KidProgressCard({
  emoji,
  title,
  subtitle,
  progress,
  index = 0,
  onPress,
}: KidProgressCardProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    card: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: t.spacing.md,
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.md,
      ...t.shadow.card,
    },
    pressed: {
      opacity: 0.92,
    },
    emoji: {
      fontSize: 36,
    },
    body: {
      flex: 1,
      gap: 4,
    },
    title: {
      fontFamily: t.fonts.bold,
      fontSize: 16,
      color: t.colors.ink,
    },
    subtitle: {
      fontFamily: t.fonts.regular,
      fontSize: 13,
      color: t.colors.inkSoft,
    },
    track: {
      marginTop: 4,
      height: 8,
      borderRadius: t.radii.pill,
      backgroundColor: t.colors.progressTrack,
      overflow: 'hidden',
    },
    fill: {
      height: '100%',
      backgroundColor: t.colors.mint,
      borderRadius: t.radii.pill,
    },
  }));

  const clamped = Math.max(0, Math.min(progress, 1));

  const content = (
    <Animated.View entering={FadeInDown.delay(index * 80).springify()} style={styles.card}>
      <Text style={styles.emoji}>{emoji}</Text>
      <View style={styles.body}>
        <Text style={styles.title}>{title}</Text>
        <Text style={styles.subtitle}>{subtitle}</Text>
        <View style={styles.track}>
          <View style={[styles.fill, { width: `${Math.round(clamped * 100)}%` }]} />
        </View>
      </View>
    </Animated.View>
  );

  if (onPress) {
    return (
      <Pressable accessibilityRole="button" onPress={onPress} style={({ pressed }) => pressed && styles.pressed}>
        {content}
      </Pressable>
    );
  }

  return content;
}
