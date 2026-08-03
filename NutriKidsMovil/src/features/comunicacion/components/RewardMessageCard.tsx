import React from 'react';
import { StyleSheet, View } from 'react-native';
import Animated, { ZoomIn } from 'react-native-reanimated';

import { theme } from '@core/theme';
import { ParentText } from '@features/familia/components/ParentText';
import { useParentTheme } from '@features/familia/providers/ParentThemeProvider';

interface RewardMessageCardProps {
  emoji: string;
  title: string;
  subtitle?: string;
}

export function RewardMessageCard({ emoji, title, subtitle }: RewardMessageCardProps): React.JSX.Element {
  const { colors } = useParentTheme();

  return (
    <Animated.View
      entering={ZoomIn.springify()}
      style={[
        styles.card,
        {
          backgroundColor: colors.surface,
          borderColor: colors.accent,
          shadowColor: colors.shadow,
        },
      ]}
    >
      <ParentText style={styles.emoji}>{emoji}</ParentText>
      <ParentText variant="h3" style={styles.title}>
        {title}
      </ParentText>
      {subtitle ? (
        <ParentText variant="bodySmall" tone="secondary" style={styles.subtitle}>
          {subtitle}
        </ParentText>
      ) : null}
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  card: {
    borderRadius: theme.radii.xl,
    padding: theme.spacing.lg,
    alignItems: 'center',
    gap: theme.spacing.sm,
    borderWidth: 2,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.12,
    shadowRadius: 10,
    elevation: 3,
  },
  emoji: { fontSize: 48 },
  title: { textAlign: 'center' },
  subtitle: { textAlign: 'center' },
});
