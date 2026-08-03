import React from 'react';
import { StyleSheet, type TextProps, type TextStyle } from 'react-native';

import { theme } from '@core/theme';
import type { TypographyVariant } from '@core/theme/typography';
import { AppText } from '@shared/components/ui/Text';

import { useParentTheme } from '../providers/ParentThemeProvider';

type ParentTextTone = 'primary' | 'secondary' | 'accent' | 'inverse';

interface ParentTextProps extends TextProps {
  variant?: TypographyVariant;
  tone?: ParentTextTone;
  children: React.ReactNode;
}

export function ParentText({
  variant = 'body',
  tone = 'primary',
  style,
  children,
  ...rest
}: ParentTextProps): React.JSX.Element {
  const { colors } = useParentTheme();

  const toneColors: Record<ParentTextTone, string> = {
    primary: colors.textPrimary,
    secondary: colors.textSecondary,
    accent: colors.accent,
    inverse: colors.textInverse,
  };

  return (
    <AppText variant={variant} style={[styles.base, { color: toneColors[tone] }, style as TextStyle]} {...rest}>
      {children}
    </AppText>
  );
}

const styles = StyleSheet.create({
  base: {
    includeFontPadding: false,
  },
});
