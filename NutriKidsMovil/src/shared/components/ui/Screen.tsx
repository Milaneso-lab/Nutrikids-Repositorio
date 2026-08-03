import React from 'react';
import { StyleSheet, View, ViewProps } from 'react-native';

import { theme } from '@core/theme';

interface ScreenProps extends ViewProps {
  children: React.ReactNode;
  padded?: boolean;
}

export function Screen({ children, padded = true, style, ...rest }: ScreenProps): React.JSX.Element {
  return (
    <View style={[styles.container, padded && styles.padded, style]} {...rest}>
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.semantic.background,
  },
  padded: {
    padding: theme.spacing.md,
  },
});
