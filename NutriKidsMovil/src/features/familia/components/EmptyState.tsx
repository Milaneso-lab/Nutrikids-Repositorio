import React from 'react';
import { StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';
import { Button } from '@shared/components/ui/Button';
import { AppText } from '@shared/components/ui/Text';

import { useParentTheme } from '../providers/ParentThemeProvider';

interface EmptyStateProps {
  icon?: string;
  title: string;
  description: string;
  actionLabel?: string;
  onAction?: () => void;
}

export function EmptyState({
  icon = '👨‍👩‍👧',
  title,
  description,
  actionLabel,
  onAction,
}: EmptyStateProps): React.JSX.Element {
  const { colors } = useParentTheme();

  return (
    <View
      style={[
        styles.container,
        { backgroundColor: colors.surface, borderColor: colors.border },
      ]}
      accessibilityRole="text"
    >
      <AppText style={styles.icon}>{icon}</AppText>
      <AppText variant="h3" style={[styles.title, { color: colors.textPrimary }]}>
        {title}
      </AppText>
      <AppText variant="bodySmall" style={[styles.description, { color: colors.textSecondary }]}>
        {description}
      </AppText>
      {actionLabel && onAction ? (
        <Button label={actionLabel} onPress={onAction} style={styles.button} />
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    alignItems: 'center',
    paddingVertical: theme.spacing['2xl'],
    paddingHorizontal: theme.spacing.lg,
    gap: theme.spacing.sm,
    borderRadius: theme.radii.xl,
    borderWidth: 1,
  },
  icon: {
    fontSize: 48,
  },
  title: {
    textAlign: 'center',
  },
  description: {
    textAlign: 'center',
  },
  button: {
    marginTop: theme.spacing.sm,
    alignSelf: 'stretch',
  },
});
