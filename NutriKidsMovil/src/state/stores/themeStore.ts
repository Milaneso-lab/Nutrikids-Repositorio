import { create } from 'zustand';

import { STORAGE_KEYS } from '@core/config/constants';
import type { ParentColorScheme } from '@core/theme/parentTheme';
import { localStorage } from '@core/storage/localStorage';

interface ThemeState {
  colorScheme: ParentColorScheme;
  hydrated: boolean;
  hydrate: () => Promise<void>;
  setColorScheme: (scheme: ParentColorScheme) => Promise<void>;
  toggleColorScheme: () => Promise<void>;
}

export const useThemeStore = create<ThemeState>((set, get) => ({
  colorScheme: 'light',
  hydrated: false,

  async hydrate() {
    const stored = await localStorage.getItem(STORAGE_KEYS.parentColorScheme);
    if (stored === 'light' || stored === 'dark') {
      set({ colorScheme: stored, hydrated: true });
      return;
    }
    set({ hydrated: true });
  },

  async setColorScheme(colorScheme) {
    await localStorage.setItem(STORAGE_KEYS.parentColorScheme, colorScheme);
    set({ colorScheme });
  },

  async toggleColorScheme() {
    const next = get().colorScheme === 'dark' ? 'light' : 'dark';
    await get().setColorScheme(next);
  },
}));
