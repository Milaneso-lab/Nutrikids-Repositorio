import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Pressable, Text, View } from 'react-native';

import { KidActionButton } from '@features/nino/components/KidActionButton';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';

const FOODS = ['🍎', '🥦', '🍌', '🥕', '🍇', '🌽'] as const;

interface Card {
  id: number;
  emoji: string;
  matched: boolean;
}

interface MemoryFoodsGameProps {
  bestScore: number;
  saving?: boolean;
  onFinish: (score: number, metadata: Record<string, unknown>) => void;
  onPlayAgain: () => void;
  onExit: () => void;
}

function buildDeck(): Card[] {
  const picked = FOODS.slice(0, 4);
  const pairs = [...picked, ...picked];
  for (let i = pairs.length - 1; i > 0; i -= 1) {
    const j = Math.floor(Math.random() * (i + 1));
    [pairs[i], pairs[j]] = [pairs[j], pairs[i]];
  }
  return pairs.map((emoji, index) => ({ id: index, emoji, matched: false }));
}

export function MemoryFoodsGame({
  bestScore,
  saving = false,
  onFinish,
  onPlayAgain,
  onExit,
}: MemoryFoodsGameProps): React.JSX.Element {
  const [cards, setCards] = useState<Card[]>(() => buildDeck());
  const [flipped, setFlipped] = useState<number[]>([]);
  const [moves, setMoves] = useState(0);
  const [seconds, setSeconds] = useState(0);
  const [finished, setFinished] = useState(false);
  const reportedRef = useRef(false);

  const styles = useThemedKidStyles((t) => ({
    wrapper: { gap: t.spacing.md },
    stats: { flexDirection: 'row', justifyContent: 'space-between', flexWrap: 'wrap', gap: t.spacing.xs },
    stat: { fontFamily: t.fonts.bold, color: t.colors.textOnGradient, fontSize: 14 },
    grid: { flexDirection: 'row', flexWrap: 'wrap', gap: t.spacing.sm, justifyContent: 'center' },
    card: {
      width: '22%',
      aspectRatio: 1,
      borderRadius: t.radii.card,
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: t.colors.surface,
      borderWidth: 2,
      borderColor: t.colors.border,
    },
    cardFlipped: { backgroundColor: t.colors.surfaceMuted, borderColor: t.colors.grape },
    cardMatched: { opacity: 0.45 },
    cardText: { fontSize: 28 },
    hidden: { fontSize: 24, color: t.colors.grape, fontFamily: t.fonts.bold },
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

  const matchedCount = useMemo(() => cards.filter((c) => c.matched).length, [cards]);
  const score = useMemo(() => Math.max(0, 1000 - moves * 25 - seconds * 2), [moves, seconds]);

  useEffect(() => {
    if (finished) {
      return;
    }
    const timer = setInterval(() => setSeconds((s) => s + 1), 1000);
    return () => clearInterval(timer);
  }, [finished]);

  useEffect(() => {
    if (flipped.length !== 2) {
      return;
    }
    const [a, b] = flipped;
    const first = cards[a];
    const second = cards[b];
    if (first.emoji === second.emoji) {
      setCards((prev) => prev.map((card, index) => (index === a || index === b ? { ...card, matched: true } : card)));
      setFlipped([]);
      return;
    }
    const timeout = setTimeout(() => setFlipped([]), 700);
    return () => clearTimeout(timeout);
  }, [cards, flipped]);

  useEffect(() => {
    if (matchedCount === cards.length && cards.length > 0 && !finished) {
      setFinished(true);
    }
  }, [cards.length, finished, matchedCount]);

  useEffect(() => {
    if (!finished || reportedRef.current) {
      return;
    }
    reportedRef.current = true;
    onFinish(score, { moves, seconds });
  }, [finished, moves, onFinish, score, seconds]);

  function handleFlip(index: number): void {
    if (finished || saving || cards[index]?.matched || flipped.includes(index) || flipped.length >= 2) {
      return;
    }
    setMoves((m) => m + 1);
    setFlipped((prev) => [...prev, index]);
  }

  function restart(): void {
    setCards(buildDeck());
    setFlipped([]);
    setMoves(0);
    setSeconds(0);
    setFinished(false);
    reportedRef.current = false;
    onPlayAgain();
  }

  return (
    <View style={styles.wrapper}>
      <View style={styles.stats}>
        <Text style={styles.stat}>Movimientos: {moves}</Text>
        <Text style={styles.stat}>Tiempo: {seconds}s</Text>
        <Text style={styles.stat}>Récord: {bestScore}</Text>
      </View>

      <View style={styles.grid}>
        {cards.map((card, index) => {
          const isVisible = card.matched || flipped.includes(index);
          return (
            <Pressable
              key={card.id}
              accessibilityRole="button"
              onPress={() => handleFlip(index)}
              style={[styles.card, isVisible && styles.cardFlipped, card.matched && styles.cardMatched]}
            >
              <Text style={isVisible ? styles.cardText : styles.hidden}>{isVisible ? card.emoji : '?'}</Text>
            </Pressable>
          );
        })}
      </View>

      {finished ? (
        <View style={styles.banner}>
          <Text style={styles.bannerTitle}>¡Lo lograste! 🎉</Text>
          <Text style={styles.bannerText}>Puntaje: {score} · {moves} movimientos en {seconds}s</Text>
          <View style={styles.actions}>
            <KidActionButton label="Jugar otra vez" emoji="🔁" variant="secondary" onPress={restart} />
          </View>
        </View>
      ) : null}

      <KidActionButton label="Salir" variant="secondary" onPress={onExit} />
    </View>
  );
}
