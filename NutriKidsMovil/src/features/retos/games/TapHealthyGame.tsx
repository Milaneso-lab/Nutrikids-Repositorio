import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Pressable, Text, View } from 'react-native';

import { KidActionButton } from '@features/nino/components/KidActionButton';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';

interface RoundOption {
  emoji: string;
  label: string;
  healthy: boolean;
}

const HEALTHY: RoundOption[] = [
  { emoji: '🍎', label: 'Manzana', healthy: true },
  { emoji: '🥦', label: 'Brócoli', healthy: true },
  { emoji: '🥕', label: 'Zanahoria', healthy: true },
  { emoji: '🍌', label: 'Plátano', healthy: true },
  { emoji: '🍇', label: 'Uvas', healthy: true },
  { emoji: '🥛', label: 'Leche', healthy: true },
];

const UNHEALTHY: RoundOption[] = [
  { emoji: '🍟', label: 'Papas fritas', healthy: false },
  { emoji: '🍭', label: 'Caramelo', healthy: false },
  { emoji: '🥤', label: 'Refresco', healthy: false },
  { emoji: '🍩', label: 'Donut', healthy: false },
  { emoji: '🍔', label: 'Hamburguesa', healthy: false },
  { emoji: '🍫', label: 'Chocolate', healthy: false },
];

interface TapHealthyGameProps {
  bestScore: number;
  saving?: boolean;
  onFinish: (score: number, metadata: Record<string, unknown>) => void;
  onPlayAgain: () => void;
  onExit: () => void;
}

function pickRound(): RoundOption[] {
  const healthy = HEALTHY[Math.floor(Math.random() * HEALTHY.length)];
  const others = UNHEALTHY.sort(() => Math.random() - 0.5).slice(0, 3);
  return [healthy, ...others].sort(() => Math.random() - 0.5);
}

export function TapHealthyGame({
  bestScore,
  saving = false,
  onFinish,
  onPlayAgain,
  onExit,
}: TapHealthyGameProps): React.JSX.Element {
  const [timeLeft, setTimeLeft] = useState(30);
  const [score, setScore] = useState(0);
  const [round, setRound] = useState<RoundOption[]>(() => pickRound());
  const [finished, setFinished] = useState(false);
  const [feedback, setFeedback] = useState<string | null>(null);
  const reportedRef = useRef(false);

  const styles = useThemedKidStyles((t) => ({
    wrapper: { gap: t.spacing.md },
    stats: { flexDirection: 'row', justifyContent: 'space-between', flexWrap: 'wrap', gap: t.spacing.xs },
    stat: { fontFamily: t.fonts.bold, color: t.colors.textOnGradient, fontSize: 14 },
    prompt: {
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.md,
      borderWidth: 1,
      borderColor: t.colors.border,
      gap: t.spacing.xs,
    },
    promptTitle: { fontFamily: t.fonts.extraBold, fontSize: 18, color: t.colors.ink, textAlign: 'center' },
    promptText: { fontFamily: t.fonts.medium, fontSize: 14, color: t.colors.inkSoft, textAlign: 'center' },
    grid: { flexDirection: 'row', flexWrap: 'wrap', gap: t.spacing.sm, justifyContent: 'center' },
    option: {
      width: '47%',
      minHeight: 96,
      borderRadius: t.radii.card,
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: t.colors.surface,
      borderWidth: 2,
      borderColor: t.colors.border,
      padding: t.spacing.sm,
      gap: 4,
    },
    optionEmoji: { fontSize: 34 },
    optionLabel: { fontFamily: t.fonts.bold, fontSize: 13, color: t.colors.ink, textAlign: 'center' },
    feedback: { fontFamily: t.fonts.bold, fontSize: 16, color: t.colors.textOnGradient, textAlign: 'center' },
    banner: {
      backgroundColor: t.colors.surface,
      borderRadius: t.radii.card,
      padding: t.spacing.md,
      gap: t.spacing.xs,
      borderWidth: 1,
      borderColor: t.colors.border,
    },
    bannerTitle: { fontFamily: t.fonts.extraBold, fontSize: 20, color: t.colors.ink, textAlign: 'center' },
    bannerText: { fontFamily: t.fonts.medium, fontSize: 14, color: t.colors.inkSoft, textAlign: 'center' },
    actions: { gap: t.spacing.sm },
  }));

  const isPlaying = useMemo(() => !finished && timeLeft > 0, [finished, timeLeft]);

  useEffect(() => {
    if (!isPlaying) {
      if (timeLeft <= 0 && !finished) {
        setFinished(true);
      }
      return;
    }
    const timer = setInterval(() => setTimeLeft((t) => t - 1), 1000);
    return () => clearInterval(timer);
  }, [finished, isPlaying, timeLeft]);

  useEffect(() => {
    if (!finished || reportedRef.current) {
      return;
    }
    reportedRef.current = true;
    onFinish(score, { aciertos: score, duracion: 30 });
  }, [finished, onFinish, score]);

  function handlePick(option: RoundOption): void {
    if (!isPlaying) {
      return;
    }
    if (option.healthy) {
      setScore((s) => s + 1);
      setFeedback('¡Correcto! 🎉');
      setRound(pickRound());
    } else {
      setFeedback('Ups, ese no es tan saludable 😅');
    }
    setTimeout(() => setFeedback(null), 500);
  }

  function restart(): void {
    setTimeLeft(30);
    setScore(0);
    setRound(pickRound());
    setFinished(false);
    setFeedback(null);
    reportedRef.current = false;
    onPlayAgain();
  }

  return (
    <View style={styles.wrapper}>
      <View style={styles.stats}>
        <Text style={styles.stat}>Tiempo: {timeLeft}s</Text>
        <Text style={styles.stat}>Aciertos: {score}</Text>
        <Text style={styles.stat}>Récord: {bestScore}</Text>
      </View>

      <View style={styles.prompt}>
        <Text style={styles.promptTitle}>Toca lo saludable</Text>
        <Text style={styles.promptText}>Elige el alimento más nutritivo antes de que se acabe el tiempo.</Text>
      </View>

      {feedback ? <Text style={styles.feedback}>{feedback}</Text> : null}

      <View style={styles.grid}>
        {round.map((option) => (
          <Pressable
            key={`${option.emoji}-${option.label}`}
            accessibilityRole="button"
            accessibilityLabel={option.label}
            onPress={() => handlePick(option)}
            style={styles.option}
          >
            <Text style={styles.optionEmoji}>{option.emoji}</Text>
            <Text style={styles.optionLabel}>{option.label}</Text>
          </Pressable>
        ))}
      </View>

      {finished ? (
        <View style={styles.banner}>
          <Text style={styles.bannerTitle}>¡Tiempo! ⏰</Text>
          <Text style={styles.bannerText}>Marcaste {score} aciertos saludables.</Text>
          <View style={styles.actions}>
            <KidActionButton label="Jugar otra vez" emoji="🔁" variant="secondary" onPress={restart} />
          </View>
        </View>
      ) : null}

      <KidActionButton label="Salir" variant="secondary" onPress={onExit} />
    </View>
  );
}
