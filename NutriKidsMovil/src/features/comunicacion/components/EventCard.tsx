import React from 'react';
import { Text, View } from 'react-native';

import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import type { Campaign } from '../types/communication.types';

interface EventCardProps {
  campaign: Campaign;
}

export function EventCard({ campaign }: EventCardProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    card: {
      flexDirection: 'row',
      gap: t.spacing.sm,
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.md,
      borderWidth: 2,
      borderColor: t.colors.sunshine,
      ...t.shadow.card,
    },
    emoji: { fontSize: 36 },
    body: { flex: 1, gap: 4 },
    title: { fontFamily: t.fonts.extraBold, fontSize: 16, color: t.colors.ink },
    description: { fontFamily: t.fonts.regular, fontSize: 13, color: t.colors.inkSoft },
    dates: { fontFamily: t.fonts.medium, fontSize: 11, color: t.colors.grape },
    reward: { fontFamily: t.fonts.semiBold, fontSize: 12, color: t.colors.mint },
  }));

  return (
    <View style={styles.card} accessibilityLabel={`Evento: ${campaign.title}`}>
      <Text style={styles.emoji}>{campaign.emoji}</Text>
      <View style={styles.body}>
        <Text style={styles.title}>{campaign.title}</Text>
        <Text style={styles.description}>{campaign.description}</Text>
        <Text style={styles.dates}>
          {campaign.startDate} → {campaign.endDate}
        </Text>
        {campaign.rewardXp ? (
          <Text style={styles.reward}>+{campaign.rewardXp} XP · +{campaign.rewardCoins ?? 0} monedas</Text>
        ) : null}
      </View>
    </View>
  );
}
