import type {
  HabitDayEntry,
  HabitDailyProgress,
  HabitMonthlyStats,
  HabitStatistics,
  HabitWeeklyStats,
  HabitoRegistro,
  NinoHabito,
} from '../../types/habits.types';
import { resolveHabitEmoji } from '../../config/habits.config';

function todayIso(): string {
  return new Date().toISOString().slice(0, 10);
}

function addDays(dateStr: string, days: number): string {
  const d = new Date(`${dateStr}T12:00:00`);
  d.setDate(d.getDate() + days);
  return d.toISOString().slice(0, 10);
}

function startOfWeek(dateStr: string): string {
  const d = new Date(`${dateStr}T12:00:00`);
  const day = d.getDay();
  const diff = day === 0 ? -6 : 1 - day;
  d.setDate(d.getDate() + diff);
  return d.toISOString().slice(0, 10);
}

function monthKey(dateStr: string): string {
  return dateStr.slice(0, 7);
}

export function buildDailyProgress(
  date: string,
  habitos: NinoHabito[],
  registros: HabitoRegistro[],
): HabitDailyProgress {
  const entries: HabitDayEntry[] = habitos
    .filter((h) => h.activo && h.catalogo)
    .map((h) => {
      const reg = registros.find((r) => r.ninoHabitoId === h.id && r.fecha === date);
      return {
        ninoHabitoId: h.id,
        habitoId: h.habitoId,
        fecha: date,
        completado: reg?.completado ?? false,
        nombre: h.catalogo!.nombre,
        emoji: resolveHabitEmoji(h.catalogo!.icono),
        categoria: h.catalogo!.categoria,
        puntosBase: h.catalogo!.puntosBase,
      };
    });

  const completed = entries.filter((e) => e.completado).length;
  return { date, total: entries.length, completed, entries };
}

export function buildWeeklyStats(
  weekStart: string,
  registros: HabitoRegistro[],
): HabitWeeklyStats {
  const days = Array.from({ length: 7 }, (_, i) => addDays(weekStart, i));
  const dailyCompletions = days.map((day) =>
    registros.filter((r) => r.fecha === day && r.completado).length,
  );
  const daysActive = dailyCompletions.filter((c) => c > 0).length;
  const habitsCompleted = dailyCompletions.reduce((a, b) => a + b, 0);
  const totalHabits = habitsCompleted;

  return {
    weekStart,
    daysActive,
    habitsCompleted,
    totalHabits,
    completionRate: totalHabits > 0 ? Math.round((habitsCompleted / Math.max(totalHabits, 1)) * 100) : 0,
  };
}

export function buildMonthlyStats(month: string, registros: HabitoRegistro[]): HabitMonthlyStats {
  const monthRegs = registros.filter((r) => r.fecha.startsWith(month) && r.completado);
  const activeDays = new Set(monthRegs.map((r) => r.fecha));
  const bestStreak = calculateBestStreak(
    registros.filter((r) => r.completado).map((r) => r.fecha),
  );

  return {
    month,
    daysWithActivity: activeDays.size,
    totalCompletions: monthRegs.length,
    bestStreak,
  };
}

export function calculateCurrentStreak(completedDates: string[]): number {
  if (completedDates.length === 0) {
    return 0;
  }

  const uniqueDays = [...new Set(completedDates)].sort((a, b) => b.localeCompare(a));
  const today = todayIso();
  let streak = 0;
  let checkDate = uniqueDays.includes(today) ? today : addDays(today, -1);

  for (let i = 0; i < 365; i++) {
    if (uniqueDays.includes(checkDate)) {
      streak++;
      checkDate = addDays(checkDate, -1);
    } else {
      break;
    }
  }

  return streak;
}

function calculateBestStreak(dates: string[]): number {
  const sorted = [...new Set(dates)].sort();
  if (sorted.length === 0) {
    return 0;
  }

  let best = 1;
  let current = 1;

  for (let i = 1; i < sorted.length; i++) {
    const prev = new Date(`${sorted[i - 1]}T12:00:00`);
    const curr = new Date(`${sorted[i]}T12:00:00`);
    const diffDays = Math.round((curr.getTime() - prev.getTime()) / 86400000);
    if (diffDays === 1) {
      current++;
      best = Math.max(best, current);
    } else if (diffDays > 1) {
      current = 1;
    }
  }

  return best;
}

export function buildStatistics(habitos: NinoHabito[], registros: HabitoRegistro[]): HabitStatistics {
  const today = todayIso();
  const weekStart = startOfWeek(today);
  const month = monthKey(today);
  const completedDates = registros.filter((r) => r.completado).map((r) => r.fecha);

  return {
    daily: buildDailyProgress(today, habitos, registros),
    weekly: buildWeeklyStats(weekStart, registros),
    monthly: buildMonthlyStats(month, registros),
    totalCompletions: completedDates.length,
    currentStreak: calculateCurrentStreak(completedDates),
  };
}

export function buildCalendarDays(
  year: number,
  month: number,
  registros: HabitoRegistro[],
  habitCount: number,
): Array<{ date: string; completed: number; total: number; hasActivity: boolean }> {
  const daysInMonth = new Date(year, month, 0).getDate();
  const result = [];

  for (let day = 1; day <= daysInMonth; day++) {
    const date = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    const dayRegs = registros.filter((r) => r.fecha === date);
    const completed = dayRegs.filter((r) => r.completado).length;
    result.push({
      date,
      completed,
      total: habitCount,
      hasActivity: dayRegs.length > 0,
    });
  }

  return result;
}
