import React, { useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { theme } from '@core/theme';
import { ParentScreen } from '@features/familia/components/ParentScreen';
import { ParentCard } from '@features/familia/components/ParentCard';
import { ParentText } from '@features/familia/components/ParentText';
import { KeyboardAwareScroll } from '@shared/components/layout/KeyboardAwareScroll';
import { Button } from '@shared/components/ui/Button';
import { ErrorMessage } from '@shared/components/ui/ErrorMessage';
import { TextInputField } from '@features/auth/components/TextInputField';
import type { FamilyStackParamList } from '@navigation/types';

import { PushNotificationPreview } from '../components/PushNotificationPreview';
import { RewardMessageCard } from '../components/RewardMessageCard';
import { useFamilyMessaging } from '../hooks/useFamilyMessaging';

type Props = NativeStackScreenProps<FamilyStackParamList, 'SendFamilyMessage'>;

export function SendFamilyMessageScreen({ route, navigation }: Props): React.JSX.Element {
  const { ninoId, childName } = route.params;
  const { templates, rewards, sending, error, success, sendTemplate, sendCustom, sendVirtualReward, clearSuccess } =
    useFamilyMessaging(ninoId, childName);
  const [customMessage, setCustomMessage] = useState('');

  return (
    <ParentScreen>
      <KeyboardAwareScroll contentContainerStyle={styles.content} keyboardVerticalOffset={88}>
        <ParentText variant="h2">Enviar mensaje a {childName}</ParentText>
        <ParentText variant="bodySmall" tone="secondary">
          Tu mensaje llegará a través de la mascota virtual. Solo palabras positivas 💚
        </ParentText>

        {success ? (
          <RewardMessageCard
            emoji="💌"
            title="¡Mensaje enviado!"
            subtitle={`${childName} lo recibirá con alegría`}
          />
        ) : null}

        <ParentCard style={styles.section}>
          <ParentText variant="h3">Felicitaciones rápidas</ParentText>
          <View style={styles.grid}>
            {templates.map((t) => (
              <Button
                key={t.id}
                label={`${t.emoji} ${t.label}`}
                variant="secondary"
                onPress={() => {
                  clearSuccess();
                  void sendTemplate(t.id);
                }}
                disabled={sending}
                style={styles.templateBtn}
              />
            ))}
          </View>
        </ParentCard>

        <ParentCard style={styles.section}>
          <ParentText variant="h3">Mensaje personalizado</ParentText>
          <TextInputField
            label="Tu mensaje"
            value={customMessage}
            onChangeText={setCustomMessage}
            multiline
            numberOfLines={3}
            placeholder="Escribe algo bonito y alentador…"
          />
          <PushNotificationPreview
            title="Mensaje de mamá/papá"
            body={customMessage || '¡Estoy orgulloso de ti!'}
            emoji="💌"
          />
          <Button
            label={sending ? 'Enviando…' : 'Enviar mensaje'}
            onPress={() => {
              clearSuccess();
              void sendCustom(customMessage);
            }}
            disabled={sending || customMessage.trim().length < 2}
            fullWidth
          />
        </ParentCard>

        <ParentCard style={styles.section}>
          <ParentText variant="h3">Recompensas virtuales</ParentText>
          <View style={styles.grid}>
            {rewards.map((r) => (
              <Button
                key={r.id}
                label={`${r.emoji} ${r.label}`}
                variant="secondary"
                onPress={() => {
                  clearSuccess();
                  void sendVirtualReward(r.id);
                }}
                disabled={sending}
                style={styles.templateBtn}
              />
            ))}
          </View>
        </ParentCard>

        {error ? <ErrorMessage message={error} /> : null}

        <Button label="Volver al perfil" variant="ghost" onPress={() => navigation.goBack()} fullWidth />
      </KeyboardAwareScroll>
    </ParentScreen>
  );
}

const styles = StyleSheet.create({
  content: {
    padding: theme.spacing.lg,
    gap: theme.spacing.md,
    paddingBottom: theme.spacing['3xl'],
  },
  section: { gap: theme.spacing.sm },
  grid: { gap: theme.spacing.sm },
  templateBtn: { alignSelf: 'stretch' },
});
