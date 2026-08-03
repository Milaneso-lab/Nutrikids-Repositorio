import React from 'react';
import { StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';
import { useParentTheme } from '@features/familia/providers/ParentThemeProvider';

import { AppText } from './Text';

interface ErrorMessageProps {
  message: string;
  title?: string;
}

export function ErrorMessage({ message, title = 'Ups' }: ErrorMessageProps): React.JSX.Element {
  const { colors, isDark } = useParentTheme();

  return (
    <View
      style={[
        styles.container,
        isDark
          ? { backgroundColor: '#3B1C1C', borderColor: '#7F1D1D' }
          : { backgroundColor: '#FFEBEE', borderColor: '#FFCDD2' },
      ]}
      accessibilityRole="alert"
    >
      <AppText variant="label" color="error">
        {title}
      </AppText>
      <AppText variant="bodySmall" style={{ color: isDark ? colors.textSecondary : theme.semantic.textSecondary }}>
        {message}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    borderRadius: theme.radii.md,
    padding: theme.spacing.sm,
    borderWidth: 1,
    gap: theme.spacing.xxs,
  },
});
