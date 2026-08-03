import React from 'react';
import { StyleSheet, Text, type TextProps, type TextStyle } from 'react-native';

import { theme } from '@core/theme';
import type { TypographyVariant } from '@core/theme/typography';
import { useAuthTheme } from '../providers/AuthThemeProvider';

type AuthTextTone = 'primary' | 'secondary' | 'accent';

interface AuthTextProps extends TextProps {
  variant?: TypographyVariant;
  tone?: AuthTextTone;
  children: React.ReactNode;
}

export function AuthText({
  variant = 'body',
  tone = 'primary',
  style,
  children,
  ...rest
}: AuthTextProps): React.JSX.Element {
  const { colors } = useAuthTheme();

  const toneColors: Record<AuthTextTone, string> = {
    primary: colors.textPrimary,
    secondary: colors.textSecondary,
    accent: colors.accent,
  };

  return (
    <Text
      style={[theme.typography[variant] as TextStyle, styles.base, { color: toneColors[tone] }, style]}
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
