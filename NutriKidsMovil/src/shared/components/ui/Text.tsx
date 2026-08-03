import React from 'react';
import { StyleSheet, Text, TextProps, TextStyle } from 'react-native';

import { theme } from '@core/theme';
import type { TypographyVariant } from '@core/theme/typography';

type TextColor = 'primary' | 'secondary' | 'inverse' | 'error' | 'success';

interface AppTextProps extends TextProps {
  variant?: TypographyVariant;
  color?: TextColor;
  children: React.ReactNode;
}

const colorMap: Record<TextColor, string> = {
  primary: theme.semantic.textPrimary,
  secondary: theme.semantic.textSecondary,
  inverse: theme.semantic.textInverse,
  error: theme.colors.error,
  success: theme.colors.success,
};

export function AppText({
  variant = 'body',
  color = 'primary',
  style,
  children,
  ...rest
}: AppTextProps): React.JSX.Element {
  return (
    <Text
      style={[theme.typography[variant] as TextStyle, styles.base, { color: colorMap[color] }, style]}
      {...rest}
    >
      {children}
    </Text>
  );
}

const styles = StyleSheet.create({
  base: {
    includeFontPadding: false,
  },
});
