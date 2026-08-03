import { apiGet, apiPost } from '@core/api/client';
import type { PaginatedResponse } from '@core/api/types';

import type { HabitoCatalogo, HabitoFrecuencia, HabitoRegistro, NinoHabito } from '../types/habits.types';

interface ApiHabitoCatalogo {
  id: number;
  nombre: string;
  descripcion: string | null;
  categoria: string;
  icono: string | null;
  puntos_base: number;
  activo: boolean;
}

interface ApiNinoHabito {
  id: number;
  nino_id: number;
  habito_id: number;
  frecuencia: string;
  asignado_por_id: number | null;
  activo: boolean;
}

interface ApiHabitoRegistro {
  id: number;
  nino_habito_id: number;
  fecha: string;
  completado: boolean;
  registrado_en: string | null;
}

function mapCatalogo(item: ApiHabitoCatalogo): HabitoCatalogo {
  return {
    id: item.id,
    nombre: item.nombre,
    descripcion: item.descripcion ?? '',
    categoria: item.categoria as HabitoCatalogo['categoria'],
    icono: item.icono ?? 'default',
    puntosBase: item.puntos_base,
    activo: item.activo,
  };
}

function mapNinoHabito(item: ApiNinoHabito, catalogo?: HabitoCatalogo): NinoHabito {
  return {
    id: item.id,
    ninoId: item.nino_id,
    habitoId: item.habito_id,
    frecuencia: item.frecuencia as HabitoFrecuencia,
    asignadoPorId: item.asignado_por_id,
    activo: item.activo,
    catalogo,
  };
}

function mapRegistro(item: ApiHabitoRegistro): HabitoRegistro {
  return {
    id: item.id,
    ninoHabitoId: item.nino_habito_id,
    fecha: item.fecha,
    completado: item.completado,
    registradoEn: item.registrado_en,
  };
}

export const habitsApi = {
  getCatalogo(page = 1, perPage = 50): Promise<PaginatedResponse<HabitoCatalogo>> {
    return apiGet<PaginatedResponse<ApiHabitoCatalogo>>('/habitos-catalogo', {
      params: { page, per_page: perPage },
    }).then((res) => ({
      ...res,
      data: res.data.map(mapCatalogo),
    }));
  },

  getNinoHabitos(ninoId: number, page = 1, perPage = 50): Promise<PaginatedResponse<NinoHabito>> {
    return apiGet<PaginatedResponse<ApiNinoHabito>>(`/ninos/${ninoId}/habitos`, {
      params: { page, per_page: perPage },
    }).then((res) => ({
      ...res,
      data: res.data.map((item) => mapNinoHabito(item)),
    }));
  },

  assignHabito(
    ninoId: number,
    habitoId: number,
    frecuencia: HabitoFrecuencia = 'diaria',
  ): Promise<NinoHabito> {
    return apiPost<ApiNinoHabito>(`/ninos/${ninoId}/habitos`, {
      habito_id: habitoId,
      frecuencia,
    }).then((item) => mapNinoHabito(item));
  },

  getRegistros(ninoId: number, page = 1, perPage = 100): Promise<PaginatedResponse<HabitoRegistro>> {
    return apiGet<PaginatedResponse<ApiHabitoRegistro>>(`/ninos/${ninoId}/habitos/registros`, {
      params: { page, per_page: perPage },
    }).then((res) => ({
      ...res,
      data: res.data.map(mapRegistro),
    }));
  },

  registrarHabito(
    ninoId: number,
    ninoHabitoId: number,
    payload: { fecha?: string; completado: boolean },
  ): Promise<HabitoRegistro> {
    return apiPost<ApiHabitoRegistro>(`/ninos/${ninoId}/habitos/${ninoHabitoId}/registrar`, payload).then(
      mapRegistro,
    );
  },
};
