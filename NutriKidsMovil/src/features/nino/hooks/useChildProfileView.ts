import { useCallback } from 'react';

import { childAuthService } from '@services/auth/childAuthService';

import { useChildSessionStore } from '../store/childSessionStore';
import { calculateAge } from '@features/familia/utils/age';
import { useProgression } from '@features/progresion/hooks/useProgression';

export function useChildProfileView() {
  const activeChild = useChildSessionStore((s) => s.activeChild);
  const isStandalone = useChildSessionStore((s) => s.isStandalone);
  const exitChildModeStore = useChildSessionStore((s) => s.exitChildMode);
  const { snapshot, loading: progressionLoading } = useProgression();

  const age = activeChild ? calculateAge(activeChild.fecha_nacimiento) : null;

  const exitChildMode = useCallback(async () => {
    if (isStandalone) {
      await childAuthService.logout();
      return;
    }
    await exitChildModeStore();
  }, [exitChildModeStore, isStandalone]);

  const badges =
    snapshot?.badges.map((badge) => ({
      id: badge.id,
      emoji: badge.emoji,
      title: badge.name,
      locked: !badge.unlocked,
    })) ?? [];

  const achievements =
    snapshot?.achievements.map((item) => ({
      id: item.id,
      emoji: item.emoji,
      title: item.name,
      description: item.description,
      locked: !item.unlocked,
    })) ?? [];

  const weeklyMission = snapshot?.missions.weekly[0];
  const weeklyProgress = weeklyMission
    ? { daysActive: weeklyMission.progress, daysTotal: weeklyMission.target }
    : { daysActive: snapshot?.streak.current ?? 0, daysTotal: 7 };

  return {
    activeChild,
    isStandalone,
    age,
    snapshot,
    progressionLoading,
    weekly: weeklyProgress,
    badges,
    achievements,
    exitChildMode,
  };
}
