export type HabitoCategoria = 'alimentacion' | 'actividad' | 'sueno' | 'higiene';
export type HabitoFrecuencia = 'diaria' | 'semanal';

export interface HabitoCatalogo {
  id: number;
  nombre: string;
  descripcion: string;
  categoria: HabitoCategoria;
  icono: string;
  puntosBase: number;
  activo: boolean;
}

export interface NinoHabito {
  id: number;
  ninoId: number;
  habitoId: number;
  frecuencia: HabitoFrecuencia;
  asignadoPorId: number | null;
  activo: boolean;
  catalogo?: HabitoCatalogo;
}

export interface HabitoRegistro {
  id: number;
  ninoHabitoId: number;
  fecha: string;
  completado: boolean;
  registradoEn: string | null;
}

export interface HabitDayEntry {
  ninoHabitoId: number;
  habitoId: number;
  fecha: string;
  completado: boolean;
  nombre: string;
  emoji: string;
  categoria: HabitoCategoria;
  puntosBase: number;
}

export interface HabitDailyProgress {
  date: string;
  total: number;
  completed: number;
  entries: HabitDayEntry[];
}

export interface HabitWeeklyStats {
  weekStart: string;
  daysActive: number;
  habitsCompleted: number;
  totalHabits: number;
  completionRate: number;
}

export interface HabitMonthlyStats {
  month: string;
  daysWithActivity: number;
  totalCompletions: number;
  bestStreak: number;
}

export interface HabitStatistics {
  daily: HabitDailyProgress;
  weekly: HabitWeeklyStats;
  monthly: HabitMonthlyStats;
  totalCompletions: number;
  currentStreak: number;
}

export interface AgeHabitRecommendation {
  minAge: number;
  maxAge: number;
  dailyTarget: number;
  message: string;
}

export interface PetReaction {
  emoji: string;
  message: string;
  mood: 'happy' | 'excited' | 'neutral';
}
