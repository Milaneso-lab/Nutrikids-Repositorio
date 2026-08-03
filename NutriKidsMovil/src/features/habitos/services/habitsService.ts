import { buildStatistics } from '../domain/calculators/habitStatsCalculator';
import { habitsRepository } from '../repositories/habitsRepository';
import type { HabitoRegistro, HabitStatistics, NinoHabito } from '../types/habits.types';

function todayIso(): string {
  return new Date().toISOString().slice(0, 10);
}

export const habitsService = {
  async loadDailyHabits(ninoId: number): Promise<{ habitos: NinoHabito[]; registros: HabitoRegistro[] }> {
    const catalogo = await habitsRepository.loadCatalogo();
    const habitos = await habitsRepository.ensureHabitsAssigned(ninoId, catalogo);
    const registros = await habitsRepository.loadRegistros(ninoId);
    return { habitos, registros };
  },

  async toggleHabit(
    ninoId: number,
    ninoHabitoId: number,
    completado: boolean,
    fecha: string = todayIso(),
  ): Promise<{ registro: HabitoRegistro; wasCompleted: boolean }> {
    const registros = await habitsRepository.loadRegistros(ninoId);
    const existing = registros.find((r) => r.ninoHabitoId === ninoHabitoId && r.fecha === fecha);
    const wasCompleted = existing?.completado ?? false;

    const registro = await habitsRepository.registerHabito(ninoId, ninoHabitoId, fecha, completado);
    return { registro, wasCompleted };
  },

  async getStatistics(ninoId: number): Promise<HabitStatistics> {
    const { habitos, registros } = await this.loadDailyHabits(ninoId);
    return buildStatistics(habitos, registros);
  },

  async getRegistrosForMonth(ninoId: number, year: number, month: number) {
    const { habitos, registros } = await this.loadDailyHabits(ninoId);
    const monthStr = `${year}-${String(month).padStart(2, '0')}`;
    const monthRegs = registros.filter((r) => r.fecha.startsWith(monthStr));
    return { habitos, registros: monthRegs, habitCount: habitos.length };
  },
};
