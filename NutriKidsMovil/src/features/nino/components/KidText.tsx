import React from 'react';
import { StyleSheet, Text, type TextProps, type TextStyle } from 'react-native';

import { useKidTheme } from '../providers/KidThemeProvider';

type KidTextTone = 'primary' | 'secondary' | 'onGradient' | 'onGradientMuted' | 'accent';

interface KidTextProps extends TextProps {
  tone?: KidTextTone;
  bold?: boolean;
  size?: number;
  children: React.ReactNode;
}

export function KidText({
  tone = 'primary',
  bold = false,
  size,
  style,
  children,
  ...rest
}: KidTextProps): React.JSX.Element {
  const { colors, theme } = useKidTheme();

  const toneColors: Record<KidTextTone, string> = {
    primary: colors.ink,
    secondary: colors.inkSoft,
    onGradient: colors.textOnGradient,
    onGradientMuted: colors.textOnGradientMuted,
    accent: colors.grape,
  };

  return (
    <Text
      style={[
        styles.base,
        {
          color: toneColors[tone],
          fontFamily: bold ? theme.fonts.bold : theme.fonts.regular,
          fontSize: size,
        },
        style,
      ]}
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
