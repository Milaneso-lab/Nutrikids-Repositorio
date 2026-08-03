import { ninosService } from '@features/familia/services/ninosService';
import type { AvatarConfig } from '@features/familia/types/familia.types';
import type { NinoWithPuntos } from '@features/familia/types/familia.types';
import type { NinoAccesoResponse } from '@features/auth/types/childAuth.types';
import { childAuthApi } from '@services/auth/childAuthApi';
import { getChildSessionMeta } from '@services/auth/sessionStorage';

import type { ChildDailySummary, ChildProfile, ChildWeeklyProgress } from '../types/nino.types';

const DEFAULT_COMPANION = '🦊';

export function mapNinoAccesoToChildProfile(response: NinoAccesoResponse): ChildProfile {
  const avatar = response.avatar_config;
  return {
    ninoId: response.nino_id,
    nombre: response.nombre,
    apellidos: response.apellidos,
    fecha_nacimiento: response.fecha_nacimiento,
    avatar_config: (avatar as ChildProfile['avatar_config']) ?? null,
    nivel: response.nivel_actual,
    puntos: response.puntos_totales,
    companion: response.companion ?? DEFAULT_COMPANION,
  };
}

export function mapNinoToChildProfile(nino: NinoWithPuntos): ChildProfile {
  const avatar = nino.avatar_config;
  return {
    ninoId: nino.id,
    nombre: nino.nombre,
    apellidos: nino.apellidos,
    fecha_nacimiento: nino.fecha_nacimiento,
    avatar_config: nino.avatar_config,
    nivel: nino.puntos?.nivel_actual ?? 1,
    puntos: nino.puntos?.puntos_totales ?? 0,
    companion: (avatar?.companion as string | undefined) ?? DEFAULT_COMPANION,
  };
}

export function buildPlaceholderDailySummary(child: ChildProfile): ChildDailySummary {
  const seed = child.ninoId % 3;
  const habitsTotal = 4;
  const habitsCompleted = seed + 1;

  return {
    habitsCompleted,
    habitsTotal,
    streakDays: 2 + seed,
    xpToday: habitsCompleted * 25,
    xpGoal: habitsTotal * 25,
    nextChallengeTitle: seed === 0 ? 'Bebe 6 vasos de agua' : seed === 1 ? 'Come una fruta' : 'Camina 20 minutos',
    nextChallengeEmoji: seed === 0 ? '💧' : seed === 1 ? '🍊' : '🚶',
  };
}

export function buildPlaceholderWeeklyProgress(child: ChildProfile): ChildWeeklyProgress {
  const seed = child.ninoId % 4;
  return {
    daysActive: 3 + seed,
    daysTotal: 7,
  };
}

export const childProfileService = {
  async loadProfile(ninoId: number): Promise<ChildProfile> {
    const detail = await ninosService.getDetail(ninoId);
    return mapNinoToChildProfile(detail);
  },

  async refreshFromApi(ninoId: number): Promise<ChildProfile> {
    const meta = await getChildSessionMeta();
    if (meta?.standalone) {
      const me = await childAuthApi.me();
      return mapNinoAccesoToChildProfile(me);
    }
    return this.loadProfile(ninoId);
  },

  async updateProfile(
    ninoId: number,
    payload: { nombre?: string; apellidos?: string; avatar_config?: AvatarConfig },
    current: ChildProfile,
  ): Promise<ChildProfile> {
    const mergedConfig = payload.avatar_config
      ? {
          ...payload.avatar_config,
          companion: payload.avatar_config.companion ?? current.companion ?? DEFAULT_COMPANION,
        }
      : undefined;

    const meta = await getChildSessionMeta();
    if (meta?.standalone) {
      const response = await childAuthApi.updateProfile({
        ...(payload.nombre ? { nombre: payload.nombre } : {}),
        ...(payload.apellidos ? { apellidos: payload.apellidos } : {}),
        ...(mergedConfig ? { avatar_config: mergedConfig as Record<string, unknown> } : {}),
      });
      return mapNinoAccesoToChildProfile(response);
    }

    const updated = await ninosService.update(ninoId, {
      ...(payload.nombre ? { nombre: payload.nombre } : {}),
      ...(payload.apellidos ? { apellidos: payload.apellidos } : {}),
      ...(mergedConfig ? { avatar_config: mergedConfig } : {}),
    });

    return {
      ...current,
      nombre: updated.nombre,
      apellidos: updated.apellidos,
      avatar_config: mergedConfig ?? current.avatar_config,
      companion: (mergedConfig?.companion as string | undefined) ?? current.companion,
    };
  },

  async updateAvatar(ninoId: number, avatarConfig: AvatarConfig, current: ChildProfile): Promise<ChildProfile> {
    return this.updateProfile(ninoId, { avatar_config: avatarConfig }, current);
  },
};
