import { withRetry } from '@shared/utils/retry';

import type {
  FamilySummary,
  Nino,
  NinoCreatePayload,
  NinoPuntos,
  NinoUpdatePayload,
  NinoWithPuntos,
} from '../types/familia.types';
import { ninosApi } from './ninosApi';

const LIST_RETRY_OPTIONS = { retries: 4, delayMs: 600, backoff: 1.8 } as const;
const PUNTOS_CONCURRENCY = 3;

let listWithPuntosInflight: Promise<{ ninos: NinoWithPuntos[]; summary: FamilySummary }> | null = null;

function buildFamilySummary(ninos: NinoWithPuntos[]): FamilySummary {
  if (ninos.length === 0) {
    return {
      totalHijos: 0,
      nivelPromedio: 0,
      puntosTotales: 0,
      ultimaActividad: null,
    };
  }

  const niveles = ninos.map((n) => n.puntos?.nivel_actual ?? 1);
  const puntos = ninos.reduce((acc, n) => acc + (n.puntos?.puntos_totales ?? 0), 0);
  const fechas = ninos
    .map((n) => n.updated_at)
    .filter((value): value is string => Boolean(value))
    .sort((a, b) => new Date(b).getTime() - new Date(a).getTime());

  return {
    totalHijos: ninos.length,
    nivelPromedio: Math.round(niveles.reduce((a, b) => a + b, 0) / niveles.length),
    puntosTotales: puntos,
    ultimaActividad: fechas[0] ?? null,
  };
}

async function attachPuntos(ninos: Nino[]): Promise<NinoWithPuntos[]> {
  const results: NinoWithPuntos[] = [];

  for (let index = 0; index < ninos.length; index += PUNTOS_CONCURRENCY) {
    const batch = ninos.slice(index, index + PUNTOS_CONCURRENCY);
    const batchResults = await Promise.all(
      batch.map(async (nino) => {
        try {
          const puntos = await withRetry(() => ninosApi.getPuntos(nino.id), { retries: 2, delayMs: 400 });
          return { ...nino, puntos };
        } catch {
          return { ...nino, puntos: null };
        }
      }),
    );
    results.push(...batchResults);
  }

  return results;
}

export const ninosService = {
  async listWithPuntos(page = 1, perPage = 50): Promise<{ ninos: NinoWithPuntos[]; summary: FamilySummary }> {
    if (listWithPuntosInflight) {
      return listWithPuntosInflight;
    }

    listWithPuntosInflight = (async () => {
      const ninos = (await withRetry(() => ninosApi.list(page, perPage), LIST_RETRY_OPTIONS)).data;
      const withPuntos = await attachPuntos(ninos);
      return { ninos: withPuntos, summary: buildFamilySummary(withPuntos) };
    })();

    try {
      return await listWithPuntosInflight;
    } finally {
      listWithPuntosInflight = null;
    }
  },

  async getById(ninoId: number): Promise<Nino> {
    return withRetry(() => ninosApi.getById(ninoId));
  },

  async getDetail(ninoId: number): Promise<NinoWithPuntos> {
    const nino = await this.getById(ninoId);
    const puntos = await this.getPuntos(ninoId);
    return { ...nino, puntos };
  },

  async create(payload: NinoCreatePayload): Promise<Nino> {
    return withRetry(() => ninosApi.create(payload));
  },

  async update(ninoId: number, payload: NinoUpdatePayload): Promise<Nino> {
    return withRetry(() => ninosApi.update(ninoId, payload));
  },

  async delete(ninoId: number): Promise<void> {
    await withRetry(() => ninosApi.delete(ninoId));
  },

  async vincularDispositivo(
    ninoId: number,
    payload: { pin: string; confirmar_pin: string },
  ): Promise<{ nino_id: number; codigo_vinculacion: string; pin_configurado: boolean }> {
    return withRetry(() => ninosApi.vincularDispositivo(ninoId, payload));
  },

  async getPuntos(ninoId: number): Promise<NinoPuntos> {
    return withRetry(() => ninosApi.getPuntos(ninoId));
  },
};
