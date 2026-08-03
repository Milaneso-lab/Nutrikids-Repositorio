import { apiGet, apiPost } from '@core/api/client';

import type { ChildGame, GameId, GameProgressResult } from '../types/challenges.types';

interface ApiChildGame {
  game_id: string;
  reto_id: number;
  nombre: string;
  descripcion: string | null;
  emoji: string;
  puntos_recompensa: number;
  best_score: number;
  last_score: number;
  plays: number;
}

interface ApiGameProgressResult {
  game_id: string;
  best_score: number;
  last_score: number;
  plays: number;
  puntos_ganados: number;
  puntos_totales: number;
  nuevo_record: boolean;
}

function mapGame(item: ApiChildGame): ChildGame {
  return {
    gameId: item.game_id as GameId,
    retoId: item.reto_id,
    nombre: item.nombre,
    descripcion: item.descripcion ?? '',
    emoji: item.emoji,
    puntosRecompensa: item.puntos_recompensa,
    bestScore: item.best_score,
    lastScore: item.last_score,
    plays: item.plays,
  };
}

function mapProgress(item: ApiGameProgressResult): GameProgressResult {
  return {
    gameId: item.game_id as GameId,
    bestScore: item.best_score,
    lastScore: item.last_score,
    plays: item.plays,
    puntosGanados: item.puntos_ganados,
    puntosTotales: item.puntos_totales,
    nuevoRecord: item.nuevo_record,
  };
}

export const challengesApi = {
  listGames(): Promise<ChildGame[]> {
    return apiGet<ApiChildGame[]>('/auth/nino/juegos').then((items) => items.map(mapGame));
  },

  saveProgress(gameId: GameId, score: number, metadata?: Record<string, unknown>): Promise<GameProgressResult> {
    return apiPost<ApiGameProgressResult>('/auth/nino/juegos/progreso', {
      game_id: gameId,
      score,
      metadata,
    }).then(mapProgress);
  },
};
