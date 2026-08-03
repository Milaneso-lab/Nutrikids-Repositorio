/** Motor de Progresión — NutriKids */
export const PROGRESION_FEATURE = 'progresion' as const;

export { progressionEngine, ProgressionEngine } from './services/progressionEngine';
export { progressionEventBus } from './events/progressionEventBus';
export { useProgression, useProgressionBootstrap } from './hooks/useProgression';
export { useProgressionStore } from './store/progressionStore';
export { ProgressionProvider } from './providers/ProgressionProvider';
export { ProgressionHud } from './components/ProgressionHud';
export { ProgressionDashboardSection } from './components/ProgressionDashboardSection';
export { ProgressionCelebrationOverlay } from './components/ProgressionCelebrationOverlay';
export type { ProgressionSnapshot, GainXpResult } from './types/progression.types';
export type { ProgressionEventType, CelebrationQueueItem } from './types/events.types';
