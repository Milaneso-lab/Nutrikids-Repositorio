import React from 'react';
import { Text, View } from 'react-native';
import Animated, { FadeInUp } from 'react-native-reanimated';

import { COMING_SOON_CONTENT } from '../constants/comingSoonContent';
import type { ComingSoonFeature } from '../types/nino.types';
import { useThemedKidStyles } from '../hooks/useThemedKidStyles';
import { CompanionMascot } from './CompanionMascot';

interface KidComingSoonViewProps {
  feature: ComingSoonFeature;
  companion?: string;
}

export function KidComingSoonView({ feature, companion = '🦊' }: KidComingSoonViewProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    container: {
      flex: 1,
      alignItems: 'center',
      justifyContent: 'center',
      padding: t.spacing.lg,
      gap: t.spacing.lg,
    },
    card: {
      width: '100%',
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.xl,
      alignItems: 'center',
      gap: t.spacing.sm,
      ...t.shadow.card,
    },
    heroEmoji: {
      fontSize: 64,
    },
    title: {
      fontFamily: t.fonts.extraBold,
      fontSize: 26,
      color: t.colors.ink,
      textAlign: 'center',
    },
    subtitle: {
      fontFamily: t.fonts.semiBold,
      fontSize: 16,
      color: t.colors.grape,
      textAlign: 'center',
    },
    divider: {
      width: 48,
      height: 4,
      borderRadius: 2,
      backgroundColor: t.colors.lavender,
      marginVertical: t.spacing.xs,
    },
    teaser: {
      fontFamily: t.fonts.regular,
      fontSize: 15,
      color: t.colors.inkSoft,
      textAlign: 'center',
      lineHeight: 22,
    },
    badge: {
      marginTop: t.spacing.sm,
      backgroundColor: t.colors.surfaceMuted,
      paddingHorizontal: t.spacing.md,
      paddingVertical: t.spacing.xs,
      borderRadius: t.radii.pill,
    },
    badgeText: {
      fontFamily: t.fonts.bold,
      fontSize: 13,
      color: t.colors.peach,
    },
  }));

  const content = COMING_SOON_CONTENT[feature];

  return (
    <View style={styles.container}>
      <Animated.View entering={FadeInUp.springify()} style={styles.card}>
        <Text style={styles.heroEmoji}>{content.emoji}</Text>
        <Text style={styles.title}>{content.title}</Text>
        <Text style={styles.subtitle}>{content.subtitle}</Text>
        <View style={styles.divider} />
        <Text style={styles.teaser}>{content.teaser}</Text>
        <View style={styles.badge}>
          <Text style={styles.badgeText}>✨ Próximamente ✨</Text>
        </View>
      </Animated.View>
      <CompanionMascot emoji={companion} message="¡Estoy preparando sorpresas para ti!" size="sm" />
    </View>
  );
}
