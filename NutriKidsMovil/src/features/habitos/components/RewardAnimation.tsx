import React, { useEffect } from 'react';
import { Modal, Pressable, Text } from 'react-native';
import Animated, { FadeIn, FadeOut, ZoomIn } from 'react-native-reanimated';

import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';

interface RewardAnimationProps {
  visible: boolean;
  emoji?: string;
  title?: string;
  subtitle?: string;
  onDismiss: () => void;
}

export function RewardAnimation({
  visible,
  emoji = '🌟',
  title = '¡Genial!',
  subtitle,
  onDismiss,
}: RewardAnimationProps): React.JSX.Element | null {
  const styles = useThemedKidStyles((t) => ({
    backdrop: {
      flex: 1,
      backgroundColor: 'rgba(15, 23, 42, 0.5)',
      alignItems: 'center',
      justifyContent: 'center',
      padding: t.spacing.lg,
    },
    card: {
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.xl,
      alignItems: 'center',
      gap: t.spacing.sm,
      minWidth: 260,
      borderWidth: 3,
      borderColor: t.colors.sunshine,
      ...t.shadow.card,
    },
    emoji: {
      fontSize: 56,
    },
    title: {
      fontFamily: t.fonts.extraBold,
      fontSize: 22,
      color: t.colors.ink,
      textAlign: 'center',
    },
    subtitle: {
      fontFamily: t.fonts.medium,
      fontSize: 14,
      color: t.colors.inkSoft,
      textAlign: 'center',
    },
  }));

  useEffect(() => {
    if (!visible) {
      return;
    }
    const timer = setTimeout(onDismiss, 2000);
    return () => clearTimeout(timer);
  }, [visible, onDismiss]);

  if (!visible) {
    return null;
  }

  return (
    <Modal transparent visible animationType="none" onRequestClose={onDismiss}>
      <Pressable style={styles.backdrop} onPress={onDismiss} accessibilityRole="button">
        <Animated.View entering={ZoomIn.springify()} exiting={FadeOut} style={styles.card}>
          <Animated.Text entering={FadeIn.delay(80)} style={styles.emoji}>
            {emoji}
          </Animated.Text>
          <Text style={styles.title}>{title}</Text>
          {subtitle ? <Text style={styles.subtitle}>{subtitle}</Text> : null}
        </Animated.View>
      </Pressable>
    </Modal>
  );
}
