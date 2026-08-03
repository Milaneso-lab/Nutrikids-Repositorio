import React from 'react';
import { Text, View } from 'react-native';
import Animated, { FadeIn, useAnimatedStyle, useSharedValue, withRepeat, withSequence, withTiming } from 'react-native-reanimated';

import { useThemedKidStyles } from '../hooks/useThemedKidStyles';

interface CompanionMascotProps {
  emoji: string;
  message?: string;
  size?: 'sm' | 'md';
}

export function CompanionMascot({ emoji, message, size = 'md' }: CompanionMascotProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    wrapper: {
      alignItems: 'center',
      gap: t.spacing.sm,
    },
    mascotBubble: {
      width: 88,
      height: 88,
      borderRadius: 44,
      backgroundColor: t.colors.surface,
      alignItems: 'center',
      justifyContent: 'center',
      borderWidth: 3,
      borderColor: t.colors.lavender,
      ...t.shadow.card,
    },
    emoji: {
      textAlign: 'center',
    },
    speech: {
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      paddingHorizontal: t.spacing.md,
      paddingVertical: t.spacing.sm,
      maxWidth: 280,
      borderWidth: 2,
      borderColor: t.colors.lavender,
    },
    message: {
      fontFamily: t.fonts.medium,
      fontSize: 14,
      color: t.colors.ink,
      textAlign: 'center',
    },
  }));

  const bounce = useSharedValue(0);

  React.useEffect(() => {
    bounce.value = withRepeat(
      withSequence(withTiming(-6, { duration: 600 }), withTiming(0, { duration: 600 })),
      -1,
      true,
    );
  }, [bounce]);

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ translateY: bounce.value }],
  }));

  const fontSize = size === 'sm' ? 40 : 56;

  return (
    <Animated.View entering={FadeIn.duration(500)} style={styles.wrapper}>
      <Animated.View style={[styles.mascotBubble, animatedStyle]}>
        <Text style={[styles.emoji, { fontSize }]} accessibilityLabel="Compañero virtual">
          {emoji}
        </Text>
      </Animated.View>
      {message ? (
        <View style={styles.speech}>
          <Text style={styles.message}>{message}</Text>
        </View>
      ) : null}
    </Animated.View>
  );
}
