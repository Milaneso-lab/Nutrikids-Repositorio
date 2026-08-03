import { create } from 'zustand';

import type { CelebrationQueueItem } from '../types/events.types';
import type { ProgressionSnapshot } from '../types/progression.types';

interface ProgressionStoreState {
  snapshot: ProgressionSnapshot | null;
  loading: boolean;
  error: string | null;
  celebrations: CelebrationQueueItem[];
  setSnapshot: (snapshot: ProgressionSnapshot | null) => void;
  setLoading: (loading: boolean) => void;
  setError: (error: string | null) => void;
  enqueueCelebrations: (items: CelebrationQueueItem[]) => void;
  dequeueCelebration: () => void;
  clearCelebrations: () => void;
  reset: () => void;
}

export const useProgressionStore = create<ProgressionStoreState>((set) => ({
  snapshot: null,
  loading: false,
  error: null,
  celebrations: [],

  setSnapshot: (snapshot) => set({ snapshot }),
  setLoading: (loading) => set({ loading }),
  setError: (error) => set({ error }),
  enqueueCelebrations: (items) =>
    set((state) => ({
      celebrations: [...state.celebrations, ...items],
    })),
  dequeueCelebration: () =>
    set((state) => ({
      celebrations: state.celebrations.slice(1),
    })),
  clearCelebrations: () => set({ celebrations: [] }),
  reset: () => set({ snapshot: null, loading: false, error: null, celebrations: [] }),
}));
