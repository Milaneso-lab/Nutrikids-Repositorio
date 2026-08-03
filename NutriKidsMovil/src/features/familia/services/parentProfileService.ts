import { apiGet, apiPatch } from '@core/api/client';

import type { AvatarConfig } from '../types/familia.types';
import { parentAvatarStorage } from '../storage/parentAvatarStorage';

export interface ParentProfileResponse {
  id_usuario: number;
  nombre: string;
  apellido_paterno: string;
  apellido_materno?: string | null;
  email: string;
  rol: string;
}

export interface ParentProfileUpdatePayload {
  nombre?: string;
  apellido_paterno?: string;
  apellido_materno?: string;
}

export const parentProfileService = {
  me(): Promise<ParentProfileResponse> {
    return apiGet<ParentProfileResponse>('/auth/me');
  },

  updateProfile(payload: ParentProfileUpdatePayload): Promise<ParentProfileResponse> {
    return apiPatch<ParentProfileResponse>('/auth/me', payload);
  },

  async getAvatar(userId: number): Promise<AvatarConfig | null> {
    return parentAvatarStorage.get(userId);
  },

  async saveAvatar(userId: number, avatar: AvatarConfig): Promise<void> {
    await parentAvatarStorage.save(userId, avatar);
  },
};
