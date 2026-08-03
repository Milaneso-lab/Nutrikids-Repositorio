import React from 'react';
import {
  ActivityIndicator,
  Pressable,
  PressableProps,
  StyleSheet,
  ViewStyle,
} from 'react-native';

import { theme } from '@core/theme';
import { useButtonThemeColors } from '@shared/theme/useButtonThemeColors';

import { AppText } from './Text';

type ButtonVariant = 'primary' | 'secondary' | 'ghost';
type ButtonSize = 'sm' | 'md' | 'lg';

interface ButtonProps extends Omit<PressableProps, 'children'> {
  label: string;
  variant?: ButtonVariant;
  size?: ButtonSize;
  loading?: boolean;
  fullWidth?: boolean;
}

const sizeStyles: Record<ButtonSize, ViewStyle> = {
  sm: { paddingVertical: theme.spacing.xs, paddingHorizontal: theme.spacing.sm },
  md: { paddingVertical: theme.spacing.sm, paddingHorizontal: theme.spacing.md },
  lg: { paddingVertical: theme.spacing.md, paddingHorizontal: theme.spacing.lg },
};

export function Button({
  label,
  variant = 'primary',
  size = 'md',
  loading = false,
  fullWidth = false,
  disabled,
  style,
  ...rest
}: ButtonProps): React.JSX.Element {
  const colors = useButtonThemeColors();
  const isDisabled = disabled || loading;

  const variantStyle =
    variant === 'primary'
      ? { backgroundColor: colors.accent }
      : variant === 'secondary'
        ? { backgroundColor: colors.accentSoft, borderWidth: 1, borderColor: colors.accentMuted }
        : { backgroundColor: 'transparent' };

  const labelColor =
    variant === 'primary' ? colors.textInverse : variant === 'secondary' ? colors.accent : colors.ghostLabel;

  return (
    <Pressable
      accessibilityRole="button"
      accessibilityState={{ disabled: isDisabled, busy: loading }}
      disabled={isDisabled}
      style={({ pressed }) => [
        styles.base,
        sizeStyles[size],
        variantStyle,
        fullWidth && styles.fullWidth,
        pressed && !isDisabled && styles.pressed,
        isDisabled && styles.disabled,
        style as ViewStyle,
      ]}
      {...rest}
    >
      {loading ? (
        <ActivityIndicator color={labelColor} />
      ) : (
        <AppText variant="button" style={[styles.label, { color: labelColor }]}>
          {label}
        </AppText>
      )}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  base: {
    borderRadius: theme.radii.md,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: 44,
  },
  fullWidth: {
    alignSelf: 'stretch',
  },
  pressed: {
    opacity: 0.88,
  },
  disabled: {
    opacity: 0.5,
  },
  label: {
    fontFamily: theme.fonts.semiBold,
  },
});
