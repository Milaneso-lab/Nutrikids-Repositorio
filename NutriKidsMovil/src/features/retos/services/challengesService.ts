import { withRetry } from '@shared/utils/retry';

import { challengesApi } from '../repositories/challengesApi';
import type { ChildGame, GameId, GameProgressResult } from '../types/challenges.types';

export const FALLBACK_GAMES: ChildGame[] = [
  {
    gameId: 'memory_foods',
    retoId: 0,
    nombre: 'Memoria de alimentos',
    descripcion: 'Encuentra las parejas de frutas y verduras',
    emoji: '🧠',
    puntosRecompensa: 20,
    bestScore: 0,
    lastScore: 0,
    plays: 0,
  },
  {
    gameId: 'tap_healthy',
    retoId: 0,
    nombre: 'Toca lo saludable',
    descripcion: 'Elige alimentos saludables lo más rápido que puedas',
    emoji: '👆',
    puntosRecompensa: 20,
    bestScore: 0,
    lastScore: 0,
    plays: 0,
  },
];

export const challengesService = {
  async listGames(): Promise<ChildGame[]> {
    try {
      const items = await challengesApi.listGames();
      return items.length > 0 ? items : FALLBACK_GAMES;
    } catch {
      return FALLBACK_GAMES;
    }
  },

  saveProgress(gameId: GameId, score: number, metadata?: Record<string, unknown>): Promise<GameProgressResult> {
    return withRetry(
      () => challengesApi.saveProgress(gameId, score, metadata),
      { retries: 2, delayMs: 900, backoff: 1.8 },
    );
  },
};
