import { create } from 'zustand';

interface UiState {
  globalLoading: boolean;
  globalError: string | null;
  setGlobalLoading: (loading: boolean) => void;
  setGlobalError: (message: string | null) => void;
  clearGlobalError: () => void;
}

export const useUiStore = create<UiState>((set) => ({
  globalLoading: false,
  globalError: null,
  setGlobalLoading: (globalLoading) => set({ globalLoading }),
  setGlobalError: (globalError) => set({ globalError }),
  clearGlobalError: () => set({ globalError: null }),
}));
