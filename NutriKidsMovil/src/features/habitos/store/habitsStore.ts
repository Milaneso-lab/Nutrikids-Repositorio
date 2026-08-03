import { create } from 'zustand';

import type { HabitoRegistro, HabitStatistics, NinoHabito } from '../types/habits.types';
import type { PetReaction } from '../types/habits.types';

interface HabitsStoreState {
  habitos: NinoHabito[];
  registros: HabitoRegistro[];
  statistics: HabitStatistics | null;
  loading: boolean;
  togglingId: number | null;
  error: string | null;
  petReaction: PetReaction | null;
  setHabitos: (habitos: NinoHabito[]) => void;
  setRegistros: (registros: HabitoRegistro[]) => void;
  setStatistics: (statistics: HabitStatistics | null) => void;
  setLoading: (loading: boolean) => void;
  setTogglingId: (id: number | null) => void;
  setError: (error: string | null) => void;
  setPetReaction: (reaction: PetReaction | null) => void;
  reset: () => void;
}

export const useHabitsStore = create<HabitsStoreState>((set) => ({
  habitos: [],
  registros: [],
  statistics: null,
  loading: false,
  togglingId: null,
  error: null,
  petReaction: null,

  setHabitos: (habitos) => set({ habitos }),
  setRegistros: (registros) => set({ registros }),
  setStatistics: (statistics) => set({ statistics }),
  setLoading: (loading) => set({ loading }),
  setTogglingId: (togglingId) => set({ togglingId }),
  setError: (error) => set({ error }),
  setPetReaction: (petReaction) => set({ petReaction }),
  reset: () =>
    set({
      habitos: [],
      registros: [],
      statistics: null,
      loading: false,
      togglingId: null,
      error: null,
      petReaction: null,
    }),
}));
