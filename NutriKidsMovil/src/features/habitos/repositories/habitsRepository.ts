import { withRetry } from '@shared/utils/retry';

import { habitsApi } from './habitsApi';
import type { HabitoCatalogo, HabitoFrecuencia, HabitoRegistro, NinoHabito } from '../types/habits.types';

export class HabitsRepository {
  async loadCatalogo(): Promise<HabitoCatalogo[]> {
    const page = await withRetry(() => habitsApi.getCatalogo());
    return page.data;
  }

  async loadNinoHabitos(ninoId: number): Promise<NinoHabito[]> {
    const [catalogo, habitosPage] = await Promise.all([
      this.loadCatalogo(),
      withRetry(() => habitsApi.getNinoHabitos(ninoId)),
    ]);

    const catalogMap = new Map(catalogo.map((c) => [c.id, c]));
    return habitosPage.data.map((h) => ({
      ...h,
      catalogo: catalogMap.get(h.habitoId),
    }));
  }

  async ensureHabitsAssigned(ninoId: number, catalogo: HabitoCatalogo[]): Promise<NinoHabito[]> {
    let habitos = await this.loadNinoHabitos(ninoId);
    if (habitos.length > 0) {
      return habitos;
    }

    const toAssign = catalogo.slice(0, 5);
    for (const item of toAssign) {
      await withRetry(() => habitsApi.assignHabito(ninoId, item.id, 'diaria'));
    }

    return this.loadNinoHabitos(ninoId);
  }

  async loadRegistros(ninoId: number): Promise<HabitoRegistro[]> {
    const page = await withRetry(() => habitsApi.getRegistros(ninoId));
    return page.data;
  }

  async registerHabito(
    ninoId: number,
    ninoHabitoId: number,
    fecha: string,
    completado: boolean,
  ): Promise<HabitoRegistro> {
    return withRetry(() =>
      habitsApi.registrarHabito(ninoId, ninoHabitoId, { fecha, completado }),
    );
  }

  async assignHabito(ninoId: number, habitoId: number, frecuencia: HabitoFrecuencia = 'diaria'): Promise<NinoHabito> {
    return withRetry(() => habitsApi.assignHabito(ninoId, habitoId, frecuencia));
  }
}

export const habitsRepository = new HabitsRepository();
