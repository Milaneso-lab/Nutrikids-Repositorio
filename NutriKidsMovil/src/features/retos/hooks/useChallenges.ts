import { useCallback, useEffect } from 'react';

import { useChallengesStore } from '../store/challengesStore';
import type { GameId } from '../types/challenges.types';

export function useChallenges() {
  const games = useChallengesStore((s) => s.games);
  const loading = useChallengesStore((s) => s.loading);
  const error = useChallengesStore((s) => s.error);
  const saving = useChallengesStore((s) => s.saving);
  const lastSaved = useChallengesStore((s) => s.lastSaved);
  const loadGames = useChallengesStore((s) => s.loadGames);
  const saveScore = useChallengesStore((s) => s.saveScore);
  const clearLastSaved = useChallengesStore((s) => s.clearLastSaved);

  const reload = useCallback(() => loadGames(), [loadGames]);

  useEffect(() => {
    if (games.length === 0 && !loading) {
      void loadGames();
    }
  }, [games.length, loadGames, loading]);

  return {
    games,
    loading,
    error,
    saving,
    lastSaved,
    reload,
    saveScore: (gameId: GameId, score: number, metadata?: Record<string, unknown>) =>
      saveScore(gameId, score, metadata),
    clearLastSaved,
  };
}
