import { create } from 'zustand';

import type { AvatarConfig } from '@features/familia/types/familia.types';
import type { AuthUser } from '@services/auth';
import type { SessionPhase } from '@shared/types/common';

interface AppState {
  sessionPhase: SessionPhase;
  isReady: boolean;
  user: AuthUser | null;
  avatarConfig: AvatarConfig | null;
  setSessionPhase: (phase: SessionPhase) => void;
  setReady: (ready: boolean) => void;
  setUser: (user: AuthUser | null) => void;
  setAvatarConfig: (avatar: AvatarConfig | null) => void;
  signIn: (user: AuthUser, avatarConfig?: AvatarConfig | null) => void;
  signOut: () => void;
}

export const useAppStore = create<AppState>((set) => ({
  sessionPhase: 'unauthenticated',
  isReady: false,
  user: null,
  avatarConfig: null,
  setSessionPhase: (sessionPhase) => set({ sessionPhase }),
  setReady: (isReady) => set({ isReady }),
  setUser: (user) => set({ user }),
  setAvatarConfig: (avatarConfig) => set({ avatarConfig }),
  signIn: (user, avatarConfig = null) => set({ user, avatarConfig, sessionPhase: 'parent' }),
  signOut: () => set({ user: null, avatarConfig: null, sessionPhase: 'unauthenticated' }),
}));
