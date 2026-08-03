import { create } from 'zustand';

import { localStorage } from '@core/storage/localStorage';
import { useAppStore } from '@state/stores/appStore';

import type { ChildProfile } from '../types/nino.types';

const STORAGE_KEY = '@nutrikids/active_child_session';

interface EnterChildModeOptions {
  standalone?: boolean;
}

interface ChildSessionState {
  activeChild: ChildProfile | null;
  isStandalone: boolean;
  hydrated: boolean;
  hydrate: () => Promise<void>;
  enterChildMode: (child: ChildProfile, options?: EnterChildModeOptions) => Promise<void>;
  exitChildMode: (options?: { standalone?: boolean }) => Promise<void>;
  updateActiveChild: (partial: Partial<ChildProfile>) => void;
}

export const useChildSessionStore = create<ChildSessionState>((set, get) => ({
  activeChild: null,
  isStandalone: false,
  hydrated: false,

  async hydrate() {
    const stored = await localStorage.getJson<ChildProfile>(STORAGE_KEY);
    if (stored) {
      set({ activeChild: stored, hydrated: true });
    } else {
      set({ hydrated: true });
    }
  },

  async enterChildMode(child, options) {
    const standalone = options?.standalone ?? false;
    await localStorage.setJson(STORAGE_KEY, child);
    set({ activeChild: child, isStandalone: standalone });
    useAppStore.getState().setSessionPhase('child');
  },

  async exitChildMode(options) {
    const standalone = options?.standalone ?? get().isStandalone;
    await localStorage.removeItem(STORAGE_KEY);
    set({ activeChild: null, isStandalone: false });
    useAppStore.getState().setSessionPhase(standalone ? 'unauthenticated' : 'parent');
  },

  updateActiveChild(partial) {
    const current = get().activeChild;
    if (!current) {
      return;
    }
    const updated = { ...current, ...partial };
    set({ activeChild: updated });
    void localStorage.setJson(STORAGE_KEY, updated);
  },
}));
