import React from 'react';
import { StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';

import { ParentCard } from './ParentCard';
import { ParentText } from './ParentText';
import { useParentTheme } from '../providers/ParentThemeProvider';

interface PlaceholderSectionProps {
  title: string;
  description: string;
  icon?: string;
}

export function PlaceholderSection({ title, description, icon = '🚧' }: PlaceholderSectionProps): React.JSX.Element {
  const { colors } = useParentTheme();

  return (
    <ParentCard style={styles.card}>
      <View style={styles.header}>
        <ParentText style={styles.icon}>{icon}</ParentText>
        <ParentText variant="h3">{title}</ParentText>
      </View>
      <ParentText variant="bodySmall" tone="secondary">
        {description}
      </ParentText>
      <ParentText
        variant="caption"
        tone="secondary"
        style={[styles.badge, { backgroundColor: colors.surfaceMuted }]}
      >
        Próximamente
      </ParentText>
    </ParentCard>
  );
}

const styles = StyleSheet.create({
  card: {
    gap: theme.spacing.sm,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: theme.spacing.sm,
  },
  icon: {
    fontSize: 22,
  },
  badge: {
    alignSelf: 'flex-start',
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: theme.spacing.xxs,
    borderRadius: theme.radii.pill,
    overflow: 'hidden',
  },
});
