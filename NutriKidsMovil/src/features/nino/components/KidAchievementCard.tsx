import React from 'react';
import { Text, View } from 'react-native';

import { useThemedKidStyles } from '../hooks/useThemedKidStyles';

interface KidAchievementCardProps {
  emoji: string;
  title: string;
  description?: string;
  locked?: boolean;
}

export function KidAchievementCard({
  emoji,
  title,
  description,
  locked = false,
}: KidAchievementCardProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    card: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: t.spacing.sm,
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.md,
      borderWidth: 2,
      borderColor: t.colors.lavender,
      minHeight: 72,
    },
    locked: {
      opacity: 0.65,
      borderColor: t.colors.border,
      backgroundColor: t.colors.surfaceMuted,
    },
    emoji: {
      fontSize: 32,
    },
    emojiLocked: {
      fontSize: 24,
    },
    body: {
      flex: 1,
      gap: 2,
    },
    title: {
      fontFamily: t.fonts.bold,
      fontSize: 15,
      color: t.colors.ink,
    },
    description: {
      fontFamily: t.fonts.regular,
      fontSize: 12,
      color: t.colors.inkSoft,
    },
    textLocked: {
      color: t.colors.inputPlaceholder,
    },
  }));

  return (
    <View
      style={[styles.card, locked && styles.locked]}
      accessibilityLabel={`${locked ? 'Bloqueado: ' : ''}${title}`}
    >
      <Text style={[styles.emoji, locked && styles.emojiLocked]}>{locked ? '🔒' : emoji}</Text>
      <View style={styles.body}>
        <Text style={[styles.title, locked && styles.textLocked]}>{title}</Text>
        {description ? (
          <Text style={[styles.description, locked && styles.textLocked]} numberOfLines={2}>
            {description}
          </Text>
        ) : null}
      </View>
    </View>
  );
}
