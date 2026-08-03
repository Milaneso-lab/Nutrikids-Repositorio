import React from 'react';
import { StyleSheet, View, type ViewProps } from 'react-native';

import { useKidTheme } from '../providers/KidThemeProvider';

interface KidCardProps extends ViewProps {
  children: React.ReactNode;
  elevated?: boolean;
}

export function KidCard({ children, elevated = true, style, ...rest }: KidCardProps): React.JSX.Element {
  const { colors, theme } = useKidTheme();

  return (
    <View
      style={[
        styles.card,
        {
          backgroundColor: colors.surface,
          borderColor: colors.border,
        },
        elevated && theme.shadow.card,
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
    borderRadius: 24,
    padding: 16,
    borderWidth: 1,
  },
});
