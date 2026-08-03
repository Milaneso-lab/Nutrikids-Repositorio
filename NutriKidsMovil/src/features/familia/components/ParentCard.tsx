import React from 'react';
import { StyleSheet, View, type ViewProps } from 'react-native';

import { theme } from '@core/theme';

import { useParentTheme } from '../providers/ParentThemeProvider';

interface ParentCardProps extends ViewProps {
  children: React.ReactNode;
  elevated?: boolean;
}

export function ParentCard({ children, elevated = true, style, ...rest }: ParentCardProps): React.JSX.Element {
  const { colors } = useParentTheme();

  return (
    <View
      style={[
        styles.card,
        {
          backgroundColor: colors.surface,
          borderColor: colors.border,
          shadowColor: colors.shadow,
        },
        elevated && styles.elevated,
        style,
      ]}
      {...rest}
    >
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    borderRadius: theme.radii.xl,
    padding: theme.spacing.md,
    borderWidth: 1,
  },
  elevated: {
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.08,
    shadowRadius: 14,
    elevation: 2,
  },
});
