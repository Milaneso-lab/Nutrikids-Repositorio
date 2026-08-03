import React, { useEffect } from 'react';
import { Text, View } from 'react-native';
import Animated, { useAnimatedStyle, useSharedValue, withTiming } from 'react-native-reanimated';

import { useThemedKidStyles } from '../hooks/useThemedKidStyles';

interface ExperienceBarProps {
  current: number;
  max: number;
  label?: string;
  showValues?: boolean;
}

export function ExperienceBar({
  current,
  max,
  label = 'Experiencia',
  showValues = true,
}: ExperienceBarProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    wrapper: {
      gap: 6,
    },
    header: {
      flexDirection: 'row',
      justifyContent: 'space-between',
      alignItems: 'center',
    },
    label: {
      fontFamily: t.fonts.semiBold,
      fontSize: 13,
      color: t.colors.inkSoft,
    },
    values: {
      fontFamily: t.fonts.bold,
      fontSize: 12,
      color: t.colors.grape,
    },
    track: {
      height: 16,
      borderRadius: t.radii.pill,
      backgroundColor: t.colors.progressTrack,
      overflow: 'hidden',
      borderWidth: 2,
      borderColor: t.colors.border,
    },
    fill: {
      height: '100%',
      backgroundColor: t.colors.grape,
      borderRadius: t.radii.pill,
    },
  }));

  const progress = max > 0 ? Math.min(current / max, 1) : 0;
  const width = useSharedValue(0);

  useEffect(() => {
    width.value = withTiming(progress, { duration: 700 });
  }, [progress, width]);

  const fillStyle = useAnimatedStyle(() => ({
    width: `${width.value * 100}%`,
  }));

  return (
    <View style={styles.wrapper} accessibilityRole="progressbar">
      <View style={styles.header}>
        <Text style={styles.label}>{label}</Text>
        {showValues ? (
          <Text style={styles.values}>
            {current}/{max} XP
          </Text>
        ) : null}
      </View>
      <View style={styles.track}>
        <Animated.View style={[styles.fill, fillStyle]} />
      </View>
    </View>
  );
}
