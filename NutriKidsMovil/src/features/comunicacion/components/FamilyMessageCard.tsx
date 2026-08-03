import React from 'react';
import { Pressable, Text, View } from 'react-native';
import Animated, { FadeInDown } from 'react-native-reanimated';

import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import { MASCOT_DELIVERY_PREFIX } from '../config/communication.config';
import type { FamilyMessage } from '../types/communication.types';

interface FamilyMessageCardProps {
  message: FamilyMessage;
  petEmoji?: string;
  index?: number;
  onPress?: () => void;
}

export function FamilyMessageCard({
  message,
  petEmoji = '🦊',
  index = 0,
  onPress,
}: FamilyMessageCardProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    card: {
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.md,
      gap: t.spacing.xs,
      borderWidth: 2,
      borderColor: t.colors.lavender,
      ...t.shadow.card,
    },
    unread: { borderColor: t.colors.mint },
    pressed: { opacity: 0.95 },
    mascotRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    petEmoji: { fontSize: 24 },
    mascotHint: { fontFamily: t.fonts.medium, fontSize: 11, color: t.colors.grape },
    emoji: { fontSize: 32, textAlign: 'center' },
    sender: { fontFamily: t.fonts.bold, fontSize: 14, color: t.colors.ink },
    content: { fontFamily: t.fonts.medium, fontSize: 15, color: t.colors.ink, textAlign: 'center' },
    newBadge: {
      fontFamily: t.fonts.bold,
      fontSize: 12,
      color: t.colors.grape,
      textAlign: 'center',
    },
  }));

  return (
    <Animated.View entering={FadeInDown.delay(index * 60).springify()}>
      <Pressable
        accessibilityRole="button"
        accessibilityLabel={`Mensaje de ${message.senderName}`}
        onPress={onPress}
        style={({ pressed }) => [styles.card, !message.read && styles.unread, pressed && styles.pressed]}
      >
        <View style={styles.mascotRow}>
          <Text style={styles.petEmoji}>{petEmoji}</Text>
          <Text style={styles.mascotHint}>{MASCOT_DELIVERY_PREFIX}</Text>
        </View>
        <Text style={styles.emoji}>{message.emoji}</Text>
        <Text style={styles.sender}>De: {message.senderName}</Text>
        <Text style={styles.content}>{message.content}</Text>
        {!message.read ? <Text style={styles.newBadge}>¡Nuevo!</Text> : null}
      </Pressable>
    </Animated.View>
  );
}
