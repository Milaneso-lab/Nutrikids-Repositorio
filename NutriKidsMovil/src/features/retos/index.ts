/** Módulo retos — minijuegos con progreso persistente */
export const RETOS_FEATURE = 'retos' as const;

export { ChildRetosScreen } from './screens/ChildRetosScreen';
export { GamePlayScreen } from './screens/GamePlayScreen';
export { useChallenges } from './hooks/useChallenges';
export type { ChildGame, GameId, GameProgressResult } from './types/challenges.types';
