import React from 'react';
import { Text, View } from 'react-native';
import Animated, { FadeIn, useAnimatedStyle, useSharedValue, withRepeat, withSequence, withTiming } from 'react-native-reanimated';

import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import type { PetReaction } from '../types/habits.types';

interface PetReactionCardProps {
  reaction: PetReaction | null;
}

export function PetReactionCard({ reaction }: PetReactionCardProps): React.JSX.Element | null {
  const styles = useThemedKidStyles((t) => ({
    wrapper: {
      alignItems: 'center',
      gap: t.spacing.sm,
      paddingVertical: t.spacing.sm,
    },
    mascotBubble: {
      width: 80,
      height: 80,
      borderRadius: 40,
      backgroundColor: t.colors.surface,
      alignItems: 'center',
      justifyContent: 'center',
      borderWidth: 3,
      borderColor: t.colors.mint,
      ...t.shadow.card,
    },
    emoji: {
      fontSize: 44,
    },
    speech: {
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      paddingHorizontal: t.spacing.md,
      paddingVertical: t.spacing.sm,
      maxWidth: 300,
      borderWidth: 2,
      borderColor: t.colors.mint,
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
    if (reaction?.mood === 'excited') {
      bounce.value = withRepeat(
        withSequence(withTiming(-8, { duration: 300 }), withTiming(0, { duration: 300 })),
        3,
        false,
      );
    }
  }, [bounce, reaction?.mood]);

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ translateY: bounce.value }],
  }));

  if (!reaction) {
    return null;
  }

  return (
    <Animated.View entering={FadeIn.duration(400)} style={styles.wrapper}>
      <Animated.View style={[styles.mascotBubble, animatedStyle]}>
        <Text style={styles.emoji} accessibilityLabel="Compañero virtual">
          {reaction.emoji}
        </Text>
      </Animated.View>
      <View style={styles.speech}>
        <Text style={styles.message}>{reaction.message}</Text>
      </View>
    </Animated.View>
  );
}
