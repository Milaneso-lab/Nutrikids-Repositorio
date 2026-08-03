import { localStorage } from '@core/storage/localStorage';

import type { AvatarConfig } from '../types/familia.types';

const KEY = '@nutrikids/parent_avatar_by_user';

type AvatarMap = Record<string, AvatarConfig>;

export const parentAvatarStorage = {
  async get(userId: number): Promise<AvatarConfig | null> {
    const map = await localStorage.getJson<AvatarMap>(KEY);
    return map?.[String(userId)] ?? null;
  },

  async save(userId: number, config: AvatarConfig): Promise<void> {
    const map = (await localStorage.getJson<AvatarMap>(KEY)) ?? {};
    map[String(userId)] = config;
    await localStorage.setJson(KEY, map);
  },
};
