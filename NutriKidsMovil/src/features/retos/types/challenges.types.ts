export type GameId = 'memory_foods' | 'tap_healthy';

export interface ChildGame {
  gameId: GameId;
  retoId: number;
  nombre: string;
  descripcion: string;
  emoji: string;
  puntosRecompensa: number;
  bestScore: number;
  lastScore: number;
  plays: number;
}

export interface GameProgressResult {
  gameId: GameId;
  bestScore: number;
  lastScore: number;
  plays: number;
  puntosGanados: number;
  puntosTotales: number;
  nuevoRecord: boolean;
}
