import { useCallback, useEffect } from 'react';

import { useChildSessionStore } from '@features/nino/store/childSessionStore';
import { calculateAge } from '@features/familia/utils/age';
import { useProgressionStore } from '@features/progresion/store/progressionStore';

import { buildDailyProgress } from '../domain/calculators/habitStatsCalculator';
import { getAgeMotivationMessage, getDailyTargetForAge } from '../domain/calculators/habitAgeRecommendations';
import { habitProgressionBridge, resolvePetReaction } from '../services/habitProgressionBridge';
import { habitsService } from '../services/habitsService';
import { useHabitsStore } from '../store/habitsStore';
import { PET_REACTION_MESSAGES } from '../config/habits.config';

function todayIso(): string {
  return new Date().toISOString().slice(0, 10);
}

export function useHabits() {
  const activeChild = useChildSessionStore((s) => s.activeChild);
  const snapshot = useProgressionStore((s) => s.snapshot);
  const setSnapshot = useProgressionStore((s) => s.setSnapshot);
  const enqueueCelebrations = useProgressionStore((s) => s.enqueueCelebrations);

  const habitos = useHabitsStore((s) => s.habitos);
  const registros = useHabitsStore((s) => s.registros);
  const statistics = useHabitsStore((s) => s.statistics);
  const loading = useHabitsStore((s) => s.loading);
  const togglingId = useHabitsStore((s) => s.togglingId);
  const error = useHabitsStore((s) => s.error);
  const petReaction = useHabitsStore((s) => s.petReaction);
  const setHabitos = useHabitsStore((s) => s.setHabitos);
  const setRegistros = useHabitsStore((s) => s.setRegistros);
  const setStatistics = useHabitsStore((s) => s.setStatistics);
  const setLoading = useHabitsStore((s) => s.setLoading);
  const setTogglingId = useHabitsStore((s) => s.setTogglingId);
  const setError = useHabitsStore((s) => s.setError);
  const setPetReaction = useHabitsStore((s) => s.setPetReaction);

  const age = activeChild ? calculateAge(activeChild.fecha_nacimiento) : null;
  const dailyTarget = getDailyTargetForAge(age);
  const motivationMessage = getAgeMotivationMessage(age);
  const daily = buildDailyProgress(todayIso(), habitos, registros);

  const load = useCallback(async () => {
    if (!activeChild) {
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const { habitos: loaded, registros: regs } = await habitsService.loadDailyHabits(activeChild.ninoId);
      setHabitos(loaded);
      setRegistros(regs);
      const stats = await habitsService.getStatistics(activeChild.ninoId);
      setStatistics(stats);

      const todayProgress = buildDailyProgress(todayIso(), loaded, regs);
      const petEmoji = activeChild.companion ?? snapshot?.pet.emoji ?? '🦊';
      const reaction = resolvePetReaction(todayProgress.completed, todayProgress.total, petEmoji);
      setPetReaction({ emoji: reaction.emoji, message: reaction.message, mood: 'happy' });
    } catch {
      setError('No pudimos cargar tus hábitos');
    } finally {
      setLoading(false);
    }
  }, [activeChild, setError, setHabitos, setLoading, setPetReaction, setRegistros, setStatistics, snapshot?.pet.emoji]);

  useEffect(() => {
    void load();
  }, [load]);

  const toggleHabit = useCallback(
    async (ninoHabitoId: number) => {
      if (!activeChild || !snapshot) {
        return;
      }

      const habito = habitos.find((h) => h.id === ninoHabitoId);
      if (!habito) {
        return;
      }

      const entry = daily.entries.find((e) => e.ninoHabitoId === ninoHabitoId);
      const newCompleted = !(entry?.completado ?? false);

      setTogglingId(ninoHabitoId);
      setError(null);

      try {
        const { registro, wasCompleted } = await habitsService.toggleHabit(
          activeChild.ninoId,
          ninoHabitoId,
          newCompleted,
        );

        const updatedRegs = [...registros.filter((r) => !(r.ninoHabitoId === ninoHabitoId && r.fecha === registro.fecha)), registro];
        setRegistros(updatedRegs);

        const stats = await habitsService.getStatistics(activeChild.ninoId);
        setStatistics(stats);

        const companion = activeChild.companion ?? '🦊';

        if (newCompleted) {
          const result = await habitProgressionBridge.onHabitToggled(
            snapshot,
            habito,
            true,
            wasCompleted,
            companion,
          );
          setSnapshot(result.snapshot);
          enqueueCelebrations(result.celebrations);

          const updatedDaily = buildDailyProgress(todayIso(), habitos, updatedRegs);
          const reaction = resolvePetReaction(updatedDaily.completed, updatedDaily.total, companion);
          setPetReaction({ emoji: companion, message: reaction.message, mood: 'excited' });
        } else {
          const msgs = PET_REACTION_MESSAGES.partial;
          setPetReaction({
            emoji: companion,
            message: msgs[Math.floor(Math.random() * msgs.length)] ?? msgs[0],
            mood: 'neutral',
          });
        }
      } catch {
        setError('No pudimos registrar el hábito. ¡Inténtalo de nuevo!');
      } finally {
        setTogglingId(null);
      }
    },
    [
      activeChild,
      daily.entries,
      enqueueCelebrations,
      habitos,
      registros,
      setError,
      setPetReaction,
      setRegistros,
      setSnapshot,
      setStatistics,
      setTogglingId,
      snapshot,
    ],
  );

  return {
    habitos,
    daily,
    statistics,
    loading,
    togglingId,
    error,
    petReaction,
    dailyTarget,
    motivationMessage,
    age,
    refresh: load,
    toggleHabit,
  };
}

export function useHabitStatistics() {
  const activeChild = useChildSessionStore((s) => s.activeChild);
  const statistics = useHabitsStore((s) => s.statistics);
  const loading = useHabitsStore((s) => s.loading);

  return {
    statistics,
    loading,
    childName: activeChild?.nombre ?? 'Aventurero',
  };
}
