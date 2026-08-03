import React from 'react';
import { ActivityIndicator, ScrollView, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { KidScreenBackground } from '@features/nino/components/KidScreenBackground';
import { HealthyActionButton } from '@features/habitos/components/HealthyActionButton';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import type { ChildStackParamList } from '@navigation/types';

import { PushNotificationPreview } from '../components/PushNotificationPreview';
import { ReminderCard } from '../components/ReminderCard';
import { usePushNotifications, useReminders } from '../hooks/useReminders';

type Props = NativeStackScreenProps<ChildStackParamList, 'RemindersSettings'>;

export function RemindersSettingsScreen({ navigation }: Props): React.JSX.Element {
  const { colors, gradients } = useKidTheme();
  const styles = useThemedKidStyles((t) => ({
    content: { padding: t.spacing.lg, paddingBottom: t.spacing['3xl'], gap: t.spacing.md },
    title: { fontFamily: t.fonts.extraBold, fontSize: 26, color: t.colors.textOnGradient },
    subtitle: { fontFamily: t.fonts.medium, fontSize: 14, color: t.colors.textOnGradientMuted },
    list: { gap: t.spacing.sm },
  }));

  const { reminders, loading, toggle } = useReminders();
  const { requestPermissions } = usePushNotifications();

  const sample = reminders[0];

  return (
    <KidScreenBackground gradient={gradients.adventure}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={styles.title}>Recordatorios amigables ⏰</Text>
        <Text style={styles.subtitle}>
          Te avisamos con mensajes positivos. Sin presión, solo cariño.
        </Text>

        {sample ? (
          <PushNotificationPreview
            title={`${sample.emoji} Recordatorio amigable`}
            body={sample.message}
            emoji={sample.emoji}
          />
        ) : null}

        {loading ? (
          <ActivityIndicator size="large" color={colors.textOnGradient} />
        ) : (
          <View style={styles.list}>
            {reminders.map((r) => (
              <ReminderCard key={r.id} reminder={r} onToggle={(enabled) => void toggle(r.id, enabled)} />
            ))}
          </View>
        )}

        <HealthyActionButton
          label="Activar notificaciones del dispositivo"
          emoji="🔔"
          variant="sunshine"
          onPress={() => void requestPermissions()}
        />

        <HealthyActionButton
          label="Volver al centro de notificaciones"
          variant="secondary"
          onPress={() => navigation.navigate('NotificationCenter')}
        />
      </ScrollView>
    </KidScreenBackground>
  );
}
