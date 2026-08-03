import React from 'react';
import { Switch, Text, View } from 'react-native';

import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import type { ReminderConfig } from '../types/communication.types';

interface ReminderCardProps {
  reminder: ReminderConfig;
  onToggle: (enabled: boolean) => void;
}

export function ReminderCard({ reminder, onToggle }: ReminderCardProps): React.JSX.Element {
  const { colors } = useKidTheme();
  const styles = useThemedKidStyles((t) => ({
    card: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: t.spacing.sm,
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.md,
      ...t.shadow.card,
    },
    emoji: { fontSize: 28 },
    body: { flex: 1, gap: 2 },
    title: { fontFamily: t.fonts.bold, fontSize: 15, color: t.colors.ink },
    message: { fontFamily: t.fonts.regular, fontSize: 12, color: t.colors.inkSoft },
    time: { fontFamily: t.fonts.semiBold, fontSize: 11, color: t.colors.grape },
  }));

  return (
    <View style={styles.card}>
      <Text style={styles.emoji}>{reminder.emoji}</Text>
      <View style={styles.body}>
        <Text style={styles.title}>{labelForKind(reminder.kind)}</Text>
        <Text style={styles.message}>{reminder.message}</Text>
        <Text style={styles.time}>
          {String(reminder.hour).padStart(2, '0')}:{String(reminder.minute).padStart(2, '0')}
        </Text>
      </View>
      <Switch
        accessibilityLabel={`Activar recordatorio ${labelForKind(reminder.kind)}`}
        value={reminder.enabled}
        onValueChange={onToggle}
        trackColor={{ true: colors.mint, false: colors.inputBorder }}
      />
    </View>
  );
}

function labelForKind(kind: ReminderConfig['kind']): string {
  const labels: Record<ReminderConfig['kind'], string> = {
    hidratacion: 'Hidratación',
    alimentacion: 'Alimentación',
    actividad: 'Actividad física',
    sueno: 'Sueño',
    mision: 'Misiones',
  };
  return labels[kind];
}
