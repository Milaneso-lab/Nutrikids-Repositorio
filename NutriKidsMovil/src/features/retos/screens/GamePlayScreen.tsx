import React, { useCallback, useRef, useState } from 'react';
import { ScrollView, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useFocusEffect } from '@react-navigation/native';

import { KidCard } from '@features/nino/components/KidCard';
import { KidScreenBackground } from '@features/nino/components/KidScreenBackground';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import type { ChildStackParamList } from '@navigation/types';

import { MemoryFoodsGame } from '../games/MemoryFoodsGame';
import { TapHealthyGame } from '../games/TapHealthyGame';
import { useChallenges } from '../hooks/useChallenges';
import { useChallengesStore } from '../store/challengesStore';
import { FALLBACK_GAMES } from '../services/challengesService';
import type { GameId, GameProgressResult } from '../types/challenges.types';

type Props = NativeStackScreenProps<ChildStackParamList, 'GamePlay'>;

export function GamePlayScreen({ navigation, route }: Props): React.JSX.Element {
  const { gameId } = route.params;
  const { gradients } = useKidTheme();
  const { games, saveScore, saving, reload } = useChallenges();
  const [saveResult, setSaveResult] = useState<GameProgressResult | null>(null);
  const [saveError, setSaveError] = useState<string | null>(null);
  const savedRef = useRef(false);
  const game = games.find((item) => item.gameId === gameId) ?? FALLBACK_GAMES.find((item) => item.gameId === gameId);

  useFocusEffect(
    useCallback(() => {
      void reload();
    }, [reload]),
  );

  const styles = useThemedKidStyles((t) => ({
    content: { padding: t.spacing.lg, paddingBottom: t.spacing['3xl'], gap: t.spacing.md },
    title: { fontFamily: t.fonts.extraBold, fontSize: 24, color: t.colors.textOnGradient, textAlign: 'center' },
    subtitle: { fontFamily: t.fonts.medium, fontSize: 14, color: t.colors.textOnGradientMuted, textAlign: 'center' },
    resultCard: { gap: t.spacing.xs },
    resultTitle: { fontFamily: t.fonts.extraBold, fontSize: 18, color: t.colors.ink, textAlign: 'center' },
    resultText: { fontFamily: t.fonts.medium, fontSize: 14, color: t.colors.inkSoft, textAlign: 'center' },
    recordHighlight: { fontFamily: t.fonts.bold, fontSize: 15, color: t.colors.mint, textAlign: 'center' },
    errorText: { fontFamily: t.fonts.medium, fontSize: 13, color: t.colors.coral, textAlign: 'center' },
    savingText: { fontFamily: t.fonts.medium, fontSize: 13, color: t.colors.textOnGradient, textAlign: 'center' },
  }));

  const handleAutoSave = useCallback(async (score: number, metadata: Record<string, unknown>): Promise<void> => {
    if (savedRef.current) {
      return;
    }
    savedRef.current = true;
    setSaveError(null);
    const result = await saveScore(gameId as GameId, score, metadata);
    if (!result) {
      savedRef.current = false;
      setSaveError(
        useChallengesStore.getState().error ?? 'No pudimos guardar tu puntaje. Sigue conectado e inténtalo otra vez.',
      );
      return;
    }
    setSaveResult(result);
    void reload();
  }, [gameId, reload, saveScore]);

  function handlePlayAgain(): void {
    savedRef.current = false;
    setSaveResult(null);
    setSaveError(null);
  }

  return (
    <KidScreenBackground gradient={gradients.adventure}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={styles.title}>{game?.emoji} {game?.nombre}</Text>
        <Text style={styles.subtitle}>{game?.descripcion}</Text>

        {saving ? <Text style={styles.savingText}>Guardando tu progreso…</Text> : null}
        {saveError ? <Text style={styles.errorText}>{saveError}</Text> : null}

        {saveResult ? (
          <KidCard style={styles.resultCard}>
            <Text style={styles.resultTitle}>
              {saveResult.nuevoRecord ? '¡Nuevo récord! 🏆' : 'Partida guardada ✅'}
            </Text>
            <Text style={styles.resultText}>
              Puntaje: {saveResult.lastScore} · Mejor marca: {saveResult.bestScore}
            </Text>
            {saveResult.nuevoRecord && saveResult.puntosGanados > 0 ? (
              <Text style={styles.recordHighlight}>
                +{saveResult.puntosGanados} puntos · Total: {saveResult.puntosTotales}
              </Text>
            ) : null}
          </KidCard>
        ) : null}

        {gameId === 'memory_foods' ? (
          <MemoryFoodsGame
            bestScore={saveResult?.bestScore ?? game?.bestScore ?? 0}
            saving={saving}
            onFinish={(score, metadata) => void handleAutoSave(score, metadata)}
            onPlayAgain={handlePlayAgain}
            onExit={() => navigation.goBack()}
          />
        ) : (
          <TapHealthyGame
            bestScore={saveResult?.bestScore ?? game?.bestScore ?? 0}
            saving={saving}
            onFinish={(score, metadata) => void handleAutoSave(score, metadata)}
            onPlayAgain={handlePlayAgain}
            onExit={() => navigation.goBack()}
          />
        )}
      </ScrollView>
    </KidScreenBackground>
  );
}
