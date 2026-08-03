import React, { useEffect } from 'react';
import { Modal, Pressable, Text } from 'react-native';
import Animated, { FadeIn, FadeOut, ZoomIn } from 'react-native-reanimated';

import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import type { CelebrationQueueItem } from '@features/progresion/types/events.types';

interface ProgressionCelebrationOverlayProps {
  item: CelebrationQueueItem | null;
  onDismiss: () => void;
}

export function ProgressionCelebrationOverlay({
  item,
  onDismiss,
}: ProgressionCelebrationOverlayProps): React.JSX.Element | null {
  const styles = useThemedKidStyles((t) => ({
    backdrop: {
      flex: 1,
      backgroundColor: 'rgba(15, 23, 42, 0.55)',
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
      ...t.shadow.card,
    },
    emoji: {
      fontSize: 56,
      textAlign: 'center',
    },
    title: {
      fontFamily: t.fonts.extraBold,
      fontSize: 24,
      color: t.colors.ink,
      textAlign: 'center',
    },
    subtitle: {
      fontFamily: t.fonts.medium,
      fontSize: 15,
      color: t.colors.inkSoft,
      textAlign: 'center',
    },
  }));

  useEffect(() => {
    if (!item) {
      return;
    }
    const timer = setTimeout(onDismiss, 2200);
    return () => clearTimeout(timer);
  }, [item, onDismiss]);

  if (!item) {
    return null;
  }

  return (
    <Modal transparent visible animationType="none" onRequestClose={onDismiss}>
      <Pressable style={styles.backdrop} onPress={onDismiss} accessibilityRole="button">
        <Animated.View entering={ZoomIn.springify()} exiting={FadeOut} style={styles.card}>
          <Animated.Text entering={FadeIn.delay(100)} style={styles.emoji}>
            {item.emoji}
          </Animated.Text>
          <Text style={styles.title}>{item.title}</Text>
          {item.subtitle ? <Text style={styles.subtitle}>{item.subtitle}</Text> : null}
        </Animated.View>
      </Pressable>
    </Modal>
  );
}
