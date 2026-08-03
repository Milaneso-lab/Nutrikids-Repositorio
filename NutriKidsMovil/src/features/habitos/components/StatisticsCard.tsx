import React from 'react';
import { Text, View } from 'react-native';

import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';

interface StatisticsCardProps {
  emoji: string;
  label: string;
  value: string | number;
  hint?: string;
  accent?: string;
}

export function StatisticsCard({
  emoji,
  label,
  value,
  hint,
  accent,
}: StatisticsCardProps): React.JSX.Element {
  const { colors } = useKidTheme();
  const styles = useThemedKidStyles((t) => ({
    card: {
      flex: 1,
      minWidth: '45%',
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.md,
      alignItems: 'center',
      gap: 4,
      borderTopWidth: 4,
      ...t.shadow.card,
    },
    emoji: {
      fontSize: 28,
    },
    value: {
      fontFamily: t.fonts.extraBold,
      fontSize: 26,
      color: t.colors.ink,
    },
    label: {
      fontFamily: t.fonts.semiBold,
      fontSize: 12,
      color: t.colors.inkSoft,
      textAlign: 'center',
    },
    hint: {
      fontFamily: t.fonts.regular,
      fontSize: 10,
      color: t.colors.inkSoft,
      textAlign: 'center',
    },
  }));

  return (
    <View style={[styles.card, { borderTopColor: accent ?? colors.lavender }]}>
      <Text style={styles.emoji}>{emoji}</Text>
      <Text style={styles.value}>{value}</Text>
      <Text style={styles.label}>{label}</Text>
      {hint ? <Text style={styles.hint}>{hint}</Text> : null}
    </View>
  );
}
