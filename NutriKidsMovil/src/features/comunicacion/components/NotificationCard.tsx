import React from 'react';
import { Pressable, Text, View } from 'react-native';
import Animated, { FadeInRight } from 'react-native-reanimated';

import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import type { AppNotification } from '../types/communication.types';

interface NotificationCardProps {
  notification: AppNotification;
  index?: number;
  onPress?: () => void;
}

export function NotificationCard({ notification, index = 0, onPress }: NotificationCardProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    card: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: t.spacing.sm,
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.md,
      minHeight: 72,
      ...t.shadow.card,
    },
    unread: {
      borderLeftWidth: 4,
      borderLeftColor: t.colors.grape,
    },
    pressed: { opacity: 0.92 },
    emoji: { fontSize: 28 },
    body: { flex: 1, gap: 2 },
    title: { fontFamily: t.fonts.bold, fontSize: 15, color: t.colors.ink },
    bodyText: { fontFamily: t.fonts.regular, fontSize: 13, color: t.colors.inkSoft },
    time: { fontFamily: t.fonts.medium, fontSize: 10, color: t.colors.inkSoft, marginTop: 2 },
    dot: { width: 10, height: 10, borderRadius: 5, backgroundColor: t.colors.coral },
  }));

  return (
    <Animated.View entering={FadeInRight.delay(index * 40).springify()}>
      <Pressable
        accessibilityRole="button"
        accessibilityLabel={`${notification.title}. ${notification.body}${notification.read ? '' : ', no leída'}`}
        onPress={onPress}
        style={({ pressed }) => [
          styles.card,
          !notification.read && styles.unread,
          pressed && styles.pressed,
        ]}
      >
        <Text style={styles.emoji}>{notification.emoji}</Text>
        <View style={styles.body}>
          <Text style={styles.title}>{notification.title}</Text>
          <Text style={styles.bodyText} numberOfLines={2}>{notification.body}</Text>
          <Text style={styles.time}>{formatTime(notification.createdAt)}</Text>
        </View>
        {!notification.read ? <View style={styles.dot} accessibilityLabel="No leída" /> : null}
      </Pressable>
    </Animated.View>
  );
}

function formatTime(iso: string): string {
  try {
    return new Intl.DateTimeFormat('es-MX', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(iso));
  } catch {
    return '';
  }
}
