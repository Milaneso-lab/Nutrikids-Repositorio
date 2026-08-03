import React from 'react';
import { Text, View } from 'react-native';

import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import type { ProgressionSnapshot } from '@features/progresion/types/progression.types';

interface ProgressionHudProps {
  snapshot: ProgressionSnapshot;
}

export function ProgressionHud({ snapshot }: ProgressionHudProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    row: {
      flexDirection: 'row',
      gap: t.spacing.sm,
      flexWrap: 'wrap',
    },
    chip: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: 4,
      backgroundColor: t.colors.surfaceElevated,
      paddingHorizontal: 10,
      paddingVertical: 6,
      borderRadius: t.radii.pill,
      minHeight: 36,
    },
    chipEmoji: {
      fontSize: 16,
    },
    chipLabel: {
      fontFamily: t.fonts.bold,
      fontSize: 14,
      color: t.colors.ink,
    },
  }));

  return (
    <View style={styles.row} accessibilityRole="summary">
      <View style={styles.chip} accessibilityLabel={`${snapshot.coins.balance} monedas`}>
        <Text style={styles.chipEmoji}>🪙</Text>
        <Text style={styles.chipLabel}>{snapshot.coins.balance}</Text>
      </View>
      <View
        style={styles.chip}
        accessibilityLabel={`Energía ${snapshot.energy.current} de ${snapshot.energy.max}`}
      >
        <Text style={styles.chipEmoji}>⚡</Text>
        <Text style={styles.chipLabel}>
          {snapshot.energy.current}/{snapshot.energy.max}
        </Text>
      </View>
      <View style={styles.chip} accessibilityLabel={`Racha ${snapshot.streak.current} días`}>
        <Text style={styles.chipEmoji}>🔥</Text>
        <Text style={styles.chipLabel}>{snapshot.streak.current}</Text>
      </View>
    </View>
  );
}
