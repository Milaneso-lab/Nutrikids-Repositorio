import React from 'react';
import { Text, View } from 'react-native';

import { useThemedKidStyles } from '../hooks/useThemedKidStyles';

interface KidFoodCardProps {
  emoji: string;
  title: string;
  tip: string;
  mealType?: string;
}

export function KidFoodCard({ emoji, title, tip, mealType }: KidFoodCardProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    card: {
      flexDirection: 'row',
      alignItems: 'flex-start',
      gap: t.spacing.sm,
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.md,
      borderWidth: 2,
      borderColor: t.colors.peach,
      minHeight: 88,
    },
    emoji: {
      fontSize: 36,
    },
    body: {
      flex: 1,
      gap: 4,
    },
    mealType: {
      fontFamily: t.fonts.semiBold,
      fontSize: 11,
      color: t.colors.coral,
      textTransform: 'uppercase',
    },
    title: {
      fontFamily: t.fonts.bold,
      fontSize: 16,
      color: t.colors.ink,
    },
    tip: {
      fontFamily: t.fonts.regular,
      fontSize: 13,
      color: t.colors.inkSoft,
    },
  }));

  return (
    <View style={styles.card} accessibilityLabel={`${title}. ${tip}`}>
      <Text style={styles.emoji}>{emoji}</Text>
      <View style={styles.body}>
        {mealType ? <Text style={styles.mealType}>{mealType}</Text> : null}
        <Text style={styles.title}>{title}</Text>
        <Text style={styles.tip}>{tip}</Text>
      </View>
    </View>
  );
}
