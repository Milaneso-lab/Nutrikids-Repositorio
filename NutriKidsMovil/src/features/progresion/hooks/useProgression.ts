import { useCallback, useEffect } from 'react';

import { useChildSessionStore } from '@features/nino/store/childSessionStore';
import { mergeSnapshotWithApiPuntos } from '@features/progresion/domain/factories/createDefaultSnapshot';
import { loadOrCreateSnapshot } from '@features/progresion/repositories/progressionRepository';
import { progressionEngine } from '@features/progresion/services/progressionEngine';
import { useProgressionStore } from '@features/progresion/store/progressionStore';

export function useProgressionBootstrap() {
  const activeChild = useChildSessionStore((s) => s.activeChild);
  const setSnapshot = useProgressionStore((s) => s.setSnapshot);
  const setLoading = useProgressionStore((s) => s.setLoading);
  const setError = useProgressionStore((s) => s.setError);
  const reset = useProgressionStore((s) => s.reset);

  const load = useCallback(async () => {
    if (!activeChild) {
      reset();
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const companion = activeChild.companion ?? '🦊';
      let snapshot = await loadOrCreateSnapshot(activeChild.ninoId, companion);

      snapshot = mergeSnapshotWithApiPuntos(snapshot, activeChild.puntos, companion);
      snapshot = await progressionEngine.initialize(snapshot, companion);

      setSnapshot(snapshot);
    } catch {
      setError('No pudimos cargar tu progreso');
    } finally {
      setLoading(false);
    }
  }, [activeChild, reset, setError, setLoading, setSnapshot]);

  useEffect(() => {
    void load();
  }, [load]);

  return { reload: load };
}

export function useProgression() {
  const snapshot = useProgressionStore((s) => s.snapshot);
  const loading = useProgressionStore((s) => s.loading);
  const error = useProgressionStore((s) => s.error);
  const celebrations = useProgressionStore((s) => s.celebrations);
  const setSnapshot = useProgressionStore((s) => s.setSnapshot);
  const enqueueCelebrations = useProgressionStore((s) => s.enqueueCelebrations);
  const dequeueCelebration = useProgressionStore((s) => s.dequeueCelebration);
  const activeChild = useChildSessionStore((s) => s.activeChild);

  const gainXp = useCallback(
    async (amount: number, action: string) => {
      if (!snapshot || !activeChild) {
        return;
      }
      const { result, celebrations: items } = progressionEngine.gainXp(
        snapshot,
        amount,
        { module: 'manual', action },
        activeChild.companion ?? '🦊',
      );
      setSnapshot(result.snapshot);
      enqueueCelebrations(items);
      await progressionEngine.persist(result.snapshot);
    },
    [activeChild, enqueueCelebrations, setSnapshot, snapshot],
  );

  const sync = useCallback(async () => {
    if (!snapshot) {
      return;
    }
    const synced = await progressionEngine.sync(snapshot);
    setSnapshot(synced);
  }, [setSnapshot, snapshot]);

  const simulateDailyProgress = useCallback(async () => {
    if (!snapshot || !activeChild) {
      return;
    }
    const companion = activeChild.companion ?? '🦊';
    let current = snapshot;

    const habitResult = progressionEngine.advanceMission(current, 'daily-habits-3', 1, companion);
    current = habitResult.snapshot;
    enqueueCelebrations(habitResult.celebrations);

    const goalResult = progressionEngine.updateDailyGoal(current, current.dailyGoal.progress + 1, companion);
    current = goalResult.snapshot;
    enqueueCelebrations(goalResult.celebrations);

    setSnapshot(current);
    await progressionEngine.persist(current);
  }, [activeChild, enqueueCelebrations, setSnapshot, snapshot]);

  return {
    snapshot,
    loading,
    error,
    celebrations,
    gainXp,
    sync,
    simulateDailyProgress,
    dequeueCelebration,
  };
}
