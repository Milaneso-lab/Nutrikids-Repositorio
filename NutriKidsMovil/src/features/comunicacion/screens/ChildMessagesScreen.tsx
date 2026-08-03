import React from 'react';
import { ActivityIndicator, ScrollView, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import Animated, { FadeInDown } from 'react-native-reanimated';

import { CompanionMascot } from '@features/nino/components/CompanionMascot';
import { KidScreenBackground } from '@features/nino/components/KidScreenBackground';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import { useChildSessionStore } from '@features/nino/store/childSessionStore';
import type { ChildStackParamList } from '@navigation/types';

import { FamilyMessageCard } from '../components/FamilyMessageCard';
import { useChildMessages } from '../hooks/useNotificationCenter';

type Props = NativeStackScreenProps<ChildStackParamList, 'ChildMessages'>;

export function ChildMessagesScreen(_props: Props): React.JSX.Element {
  const { colors, gradients } = useKidTheme();
  const styles = useThemedKidStyles((t) => ({
    content: { padding: t.spacing.lg, paddingBottom: t.spacing['3xl'], gap: t.spacing.md },
    title: { fontFamily: t.fonts.extraBold, fontSize: 26, color: t.colors.textOnGradient },
    subtitle: { fontFamily: t.fonts.medium, fontSize: 14, color: t.colors.textOnGradientMuted },
    list: { gap: t.spacing.md },
    empty: {
      fontFamily: t.fonts.medium,
      fontSize: 14,
      color: t.colors.textOnGradient,
      textAlign: 'center',
      padding: t.spacing.lg,
    },
  }));

  const activeChild = useChildSessionStore((s) => s.activeChild);
  const { messages, loading, markRead } = useChildMessages();
  const petEmoji = activeChild?.companion ?? '🦊';

  return (
    <KidScreenBackground gradient={gradients.home}>
      <ScrollView contentContainerStyle={styles.content}>
        <Animated.View entering={FadeInDown.springify()}>
          <Text style={styles.title}>Mensajes de mi familia 💌</Text>
          <Text style={styles.subtitle}>Tu compañero te los trae con cariño</Text>
        </Animated.View>

        <CompanionMascot
          emoji={petEmoji}
          message={
            messages.some((m) => !m.read)
              ? '¡Tienes mensajes nuevos! Léelos con alegría'
              : 'Tu familia te quiere mucho 💚'
          }
        />

        {loading ? (
          <ActivityIndicator size="large" color={colors.textOnGradient} />
        ) : (
          <View style={styles.list}>
            {messages.map((msg, i) => (
              <FamilyMessageCard
                key={msg.id}
                message={msg}
                petEmoji={petEmoji}
                index={i}
                onPress={() => void markRead(msg.id)}
              />
            ))}
            {messages.length === 0 ? (
              <Text style={styles.empty}>
                Cuando mamá o papá te envíen un mensaje, aparecerá aquí 🌈
              </Text>
            ) : null}
          </View>
        )}
      </ScrollView>
    </KidScreenBackground>
  );
}
