import React from 'react';
import { ActivityIndicator, Pressable, ScrollView, Text, View } from 'react-native';
import Animated, { FadeInDown } from 'react-native-reanimated';
import { CompositeScreenProps, useFocusEffect } from '@react-navigation/native';
import { BottomTabScreenProps } from '@react-navigation/bottom-tabs';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useCallback } from 'react';

import { KidCard } from '@features/nino/components/KidCard';
import { KidScreenBackground } from '@features/nino/components/KidScreenBackground';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import type { ChildStackParamList, ChildTabParamList } from '@navigation/types';

import { useChallenges } from '../hooks/useChallenges';
import type { GameId } from '../types/challenges.types';

type Props = CompositeScreenProps<
  BottomTabScreenProps<ChildTabParamList, 'Retos'>,
  NativeStackScreenProps<ChildStackParamList>
>;

export function ChildRetosScreen({ navigation }: Props): React.JSX.Element {
  const { gradients } = useKidTheme();
  const { games, loading, error, reload } = useChallenges();

  useFocusEffect(
    useCallback(() => {
      void reload();
    }, [reload]),
  );
  const styles = useThemedKidStyles((t) => ({
    content: {
      padding: t.spacing.lg,
      paddingBottom: t.spacing['3xl'],
      gap: t.spacing.md,
    },
    center: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: t.spacing.sm },
    title: { fontFamily: t.fonts.extraBold, fontSize: 28, color: t.colors.textOnGradient },
    subtitle: { fontFamily: t.fonts.medium, fontSize: 15, color: t.colors.textOnGradientMuted },
    list: { gap: t.spacing.md },
    card: { gap: t.spacing.sm },
    cardHeader: { flexDirection: 'row', alignItems: 'center', gap: t.spacing.sm },
    emoji: { fontSize: 32 },
    cardTitle: { fontFamily: t.fonts.bold, fontSize: 18, color: t.colors.ink, flex: 1 },
    cardText: { fontFamily: t.fonts.regular, fontSize: 14, color: t.colors.inkSoft },
    record: { fontFamily: t.fonts.semiBold, fontSize: 13, color: t.colors.mint },
    lastScore: { fontFamily: t.fonts.medium, fontSize: 13, color: t.colors.inkSoft },
    playBtn: {
      marginTop: t.spacing.xs,
      backgroundColor: t.colors.mint,
      borderRadius: t.radii.button,
      paddingVertical: t.spacing.sm,
      alignItems: 'center',
    },
    playLabel: { fontFamily: t.fonts.extraBold, fontSize: 16, color: t.colors.ink },
    error: { fontFamily: t.fonts.medium, fontSize: 14, color: t.colors.textOnGradient, textAlign: 'center' },
    retry: { fontFamily: t.fonts.bold, fontSize: 14, color: t.colors.textOnGradient, textAlign: 'center' },
  }));

  function openGame(gameId: GameId): void {
    navigation.navigate('GamePlay', { gameId });
  }

  return (
    <KidScreenBackground gradient={gradients.adventure}>
      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color="#FFFFFF" />
        </View>
      ) : (
        <ScrollView contentContainerStyle={styles.content}>
          <Text style={styles.title}>Mis Retos 🎯</Text>
          <Text style={styles.subtitle}>Juega, diviértete y supera tu récord para ganar puntos.</Text>

          {error ? (
            <View>
              <Text style={styles.error}>{error}</Text>
              <Pressable accessibilityRole="button" onPress={() => void reload()}>
                <Text style={styles.retry}>Reintentar</Text>
              </Pressable>
            </View>
          ) : null}

          <View style={styles.list}>
            {games.map((game, index) => (
              <Animated.View key={game.gameId} entering={FadeInDown.delay(index * 80).springify()}>
                <KidCard style={styles.card}>
                  <View style={styles.cardHeader}>
                    <Text style={styles.emoji}>{game.emoji}</Text>
                    <Text style={styles.cardTitle}>{game.nombre}</Text>
                  </View>
                  <Text style={styles.cardText}>{game.descripcion}</Text>
                  <Text style={styles.record}>🏆 Récord: {game.bestScore}</Text>
                  <Text style={styles.lastScore}>
                    Última partida: {game.plays > 0 ? game.lastScore : '—'} · Jugadas: {game.plays}
                  </Text>
                  <Pressable accessibilityRole="button" style={styles.playBtn} onPress={() => openGame(game.gameId)}>
                    <Text style={styles.playLabel}>Jugar ahora</Text>
                  </Pressable>
                </KidCard>
              </Animated.View>
            ))}
          </View>
        </ScrollView>
      )}
    </KidScreenBackground>
  );
}
