import React from 'react';
import { Pressable, Text } from 'react-native';

import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';

interface HealthyActionButtonProps {
  label: string;
  emoji?: string;
  variant?: 'primary' | 'secondary' | 'sunshine';
  onPress?: () => void;
  disabled?: boolean;
}

export function HealthyActionButton({
  label,
  emoji,
  variant = 'primary',
  onPress,
  disabled = false,
}: HealthyActionButtonProps): React.JSX.Element {
  const { colors } = useKidTheme();
  const styles = useThemedKidStyles((t) => ({
    button: {
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'center',
      gap: t.spacing.sm,
      minHeight: 52,
      borderRadius: t.radii.button,
      paddingHorizontal: t.spacing.lg,
      paddingVertical: t.spacing.md,
      ...t.shadow.button,
    },
    pressed: {
      opacity: 0.9,
      transform: [{ scale: 0.98 }],
    },
    disabled: {
      opacity: 0.5,
    },
    emoji: {
      fontSize: 22,
    },
    label: {
      fontFamily: t.fonts.bold,
      fontSize: 16,
    },
  }));

  const variantColors = {
    primary: { bg: colors.grape, text: colors.textOnGradient },
    secondary: { bg: colors.surface, text: colors.ink },
    sunshine: { bg: colors.sunshine, text: colors.ink },
  }[variant];

  return (
    <Pressable
      accessibilityRole="button"
      accessibilityLabel={label}
      accessibilityState={{ disabled }}
      onPress={onPress}
      disabled={disabled || !onPress}
      style={({ pressed }) => [
        styles.button,
        { backgroundColor: variantColors.bg },
        pressed && styles.pressed,
        disabled && styles.disabled,
      ]}
    >
      {emoji ? <Text style={styles.emoji}>{emoji}</Text> : null}
      <Text style={[styles.label, { color: variantColors.text }]}>{label}</Text>
    </Pressable>
  );
}
