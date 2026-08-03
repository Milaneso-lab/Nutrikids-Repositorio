import React from 'react';
import { ActivityIndicator, Pressable, StyleSheet, Text, ViewStyle } from 'react-native';
import Animated, { useAnimatedStyle, useSharedValue, withSpring } from 'react-native-reanimated';

import { useKidTheme } from '../providers/KidThemeProvider';

const AnimatedPressable = Animated.createAnimatedComponent(Pressable);

type KidActionVariant = 'primary' | 'secondary' | 'sunshine' | 'mint' | 'coral';

interface KidActionButtonProps {
  label: string;
  emoji?: string;
  onPress: () => void;
  loading?: boolean;
  disabled?: boolean;
  variant?: KidActionVariant;
  style?: ViewStyle;
}

export function KidActionButton({
  label,
  emoji,
  onPress,
  loading = false,
  disabled = false,
  variant = 'primary',
  style,
}: KidActionButtonProps): React.JSX.Element {
  const { colors, theme } = useKidTheme();
  const scale = useSharedValue(1);

  const variantStyles: Record<KidActionVariant, { bg: string; text: string; border: string }> = {
    primary: { bg: colors.grape, text: colors.textOnGradient, border: colors.border },
    secondary: { bg: colors.surface, text: colors.grape, border: colors.grape },
    sunshine: { bg: colors.sunshine, text: colors.ink, border: colors.border },
    mint: { bg: colors.mint, text: colors.ink, border: colors.border },
    coral: { bg: colors.coral, text: colors.textOnGradient, border: colors.border },
  };

  const variantColors = variantStyles[variant];

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: scale.value }],
  }));

  return (
    <AnimatedPressable
      accessibilityRole="button"
      accessibilityLabel={label}
      accessibilityState={{ disabled: disabled || loading, busy: loading }}
      disabled={disabled || loading}
      onPressIn={() => {
        scale.value = withSpring(0.96, { damping: 15 });
      }}
      onPressOut={() => {
        scale.value = withSpring(1, { damping: 12 });
      }}
      onPress={onPress}
      style={[
        styles.button,
        { backgroundColor: variantColors.bg, borderColor: variantColors.border },
        theme.shadow.button,
        animatedStyle,
        style,
      ]}
    >
      {loading ? (
        <ActivityIndicator color={variantColors.text} />
      ) : (
        <>
          {emoji ? <Text style={styles.emoji}>{emoji}</Text> : null}
          <Text style={[styles.label, { color: variantColors.text, fontFamily: theme.fonts.extraBold }]}>
            {label}
          </Text>
        </>
      )}
    </AnimatedPressable>
  );
}

const styles = StyleSheet.create({
  button: {
    minHeight: 56,
    borderRadius: 20,
    paddingHorizontal: 24,
    paddingVertical: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 12,
    borderWidth: 3,
  },
  emoji: {
    fontSize: 22,
  },
  label: {
    fontSize: 17,
  },
});
