import React from 'react';
import { StyleSheet, View, ViewProps } from 'react-native';

import { theme } from '@core/theme';

interface CardProps extends ViewProps {
  children: React.ReactNode;
  elevated?: boolean;
}

export function Card({ children, elevated = true, style, ...rest }: CardProps): React.JSX.Element {
  return (
    <View style={[styles.card, elevated && theme.shadows.sm, style]} {...rest}>
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: theme.semantic.surface,
    borderRadius: theme.radii.lg,
    padding: theme.spacing.md,
    borderWidth: 1,
    borderColor: theme.semantic.border,
  },
});
