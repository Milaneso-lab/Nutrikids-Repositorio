import { create } from 'zustand';

import { getFriendlyErrorMessage } from '@core/errors/friendlyMessages';

import { challengesService } from '../services/challengesService';
import type { ChildGame, GameId, GameProgressResult } from '../types/challenges.types';

interface ChallengesState {
  games: ChildGame[];
  loading: boolean;
  error: string | null;
  saving: boolean;
  lastSaved: GameProgressResult | null;
  loadGames: () => Promise<void>;
  saveScore: (gameId: GameId, score: number, metadata?: Record<string, unknown>) => Promise<GameProgressResult | null>;
  clearLastSaved: () => void;
}

export const useChallengesStore = create<ChallengesState>((set, get) => ({
  games: [],
  loading: false,
  error: null,
  saving: false,
  lastSaved: null,

  async loadGames() {
    set({ loading: true, error: null });
    try {
      const items = await challengesService.listGames();
      set({ games: items, loading: false });
    } catch (err) {
      set({
        loading: false,
        error: err instanceof Error ? err.message : 'No se pudieron cargar los retos',
      });
    }
  },

  async saveScore(gameId, score, metadata) {
    set({ saving: true, error: null });
    try {
      const result = await challengesService.saveProgress(gameId, score, metadata);
      set((state) => {
        const exists = state.games.some((game) => game.gameId === gameId);
        const updatedGames = exists
          ? state.games.map((game) =>
              game.gameId === gameId
                ? {
                    ...game,
                    bestScore: result.bestScore,
                    lastScore: result.lastScore,
                    plays: result.plays,
                  }
                : game,
            )
          : [
              ...state.games,
              {
                gameId,
                retoId: 0,
                nombre: gameId,
                descripcion: '',
                emoji: '🎮',
                puntosRecompensa: 0,
                bestScore: result.bestScore,
                lastScore: result.lastScore,
                plays: result.plays,
              },
            ];

        return {
          saving: false,
          lastSaved: result,
          games: updatedGames,
        };
      });
      return result;
    } catch (err) {
      const message = getFriendlyErrorMessage(err);
      set({
        saving: false,
        error: message,
      });
      return null;
    }
  },

  clearLastSaved() {
    set({ lastSaved: null });
  },
}));
