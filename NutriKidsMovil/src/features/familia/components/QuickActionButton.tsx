import React from 'react';
import { Pressable, StyleSheet } from 'react-native';

import { theme } from '@core/theme';
import { AppText } from '@shared/components/ui/Text';

import { useParentTheme } from '../providers/ParentThemeProvider';

interface QuickActionButtonProps {
  icon: string;
  label: string;
  onPress: () => void;
  variant?: 'primary' | 'neutral';
}

export function QuickActionButton({
  icon,
  label,
  onPress,
  variant = 'neutral',
}: QuickActionButtonProps): React.JSX.Element {
  const { colors } = useParentTheme();
  const isPrimary = variant === 'primary';

  return (
    <Pressable
      accessibilityRole="button"
      accessibilityLabel={label}
      onPress={onPress}
      style={({ pressed }) => [
        styles.button,
        isPrimary
          ? { backgroundColor: colors.accent, borderColor: colors.accent }
          : { backgroundColor: colors.surface, borderColor: colors.border },
        pressed && styles.pressed,
      ]}
    >
      <AppText style={styles.icon}>{icon}</AppText>
      <AppText
        variant="caption"
        style={[
          styles.label,
          isPrimary ? styles.primaryLabel : { color: colors.textPrimary },
        ]}
      >
        {label}
      </AppText>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  button: {
    flex: 1,
    minHeight: 92,
    borderRadius: theme.radii.xl,
    padding: theme.spacing.sm,
    alignItems: 'center',
    justifyContent: 'center',
    gap: theme.spacing.xxs,
    borderWidth: 1,
  },
  pressed: {
    opacity: 0.92,
    transform: [{ scale: 0.98 }],
  },
  icon: {
    fontSize: 24,
  },
  label: {
    fontFamily: theme.fonts.semiBold,
    textAlign: 'center',
  },
  primaryLabel: {
    color: '#FFFFFF',
  },
});
