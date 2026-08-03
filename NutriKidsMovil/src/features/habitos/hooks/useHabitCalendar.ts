import React, { useCallback, useEffect, useState } from 'react';

import { useChildSessionStore } from '@features/nino/store/childSessionStore';
import { buildCalendarDays } from '../domain/calculators/habitStatsCalculator';
import { habitsService } from '../services/habitsService';

export function useHabitCalendar() {
  const activeChild = useChildSessionStore((s) => s.activeChild);
  const now = new Date();
  const [year, setYear] = useState(now.getFullYear());
  const [month, setMonth] = useState(now.getMonth() + 1);
  const [days, setDays] = useState<ReturnType<typeof buildCalendarDays>>([]);
  const [loading, setLoading] = useState(false);
  const [streak, setStreak] = useState(0);

  const load = useCallback(async () => {
    if (!activeChild) {
      return;
    }

    setLoading(true);
    try {
      const { habitos, registros, habitCount } = await habitsService.getRegistrosForMonth(
        activeChild.ninoId,
        year,
        month,
      );
      const stats = await habitsService.getStatistics(activeChild.ninoId);
      setStreak(stats.currentStreak);
      setDays(buildCalendarDays(year, month, registros, habitCount || habitos.length));
    } finally {
      setLoading(false);
    }
  }, [activeChild, month, year]);

  useEffect(() => {
    void load();
  }, [load]);

  const goToPrevMonth = useCallback(() => {
    if (month === 1) {
      setYear((y) => y - 1);
      setMonth(12);
    } else {
      setMonth((m) => m - 1);
    }
  }, [month]);

  const goToNextMonth = useCallback(() => {
    if (month === 12) {
      setYear((y) => y + 1);
      setMonth(1);
    } else {
      setMonth((m) => m + 1);
    }
  }, [month]);

  return { year, month, days, loading, streak, goToPrevMonth, goToNextMonth, refresh: load };
}
