import { useEffect, useState } from 'react';

import { useChildSessionStore } from '@features/nino/store/childSessionStore';
import { parentAvatarStorage } from '@features/familia/storage/parentAvatarStorage';
import { authService } from '@services/auth';
import { childAuthService } from '@services/auth/childAuthService';
import { useAppStore } from '@state/stores/appStore';

export type BootstrapResult = 'authenticated' | 'onboarding' | 'welcome';

const BOOTSTRAP_TIMEOUT_MS = 12_000;

export function useAuthBootstrap() {
  const setUser = useAppStore((state) => state.setUser);
  const setSessionPhase = useAppStore((state) => state.setSessionPhase);
  const hydrateChild = useChildSessionStore((state) => state.hydrate);
  const [result, setResult] = useState<BootstrapResult | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let mounted = true;
    let timedOut = false;

    const timeoutId = setTimeout(() => {
      if (!mounted) {
        return;
      }
      timedOut = true;
      setSessionPhase('unauthenticated');
      setResult('welcome');
      setLoading(false);
    }, BOOTSTRAP_TIMEOUT_MS);

    async function bootstrap(): Promise<void> {
      try {
        const standaloneProfile = await childAuthService.restoreStandaloneSession();
        if (!mounted || timedOut) {
          return;
        }

        if (standaloneProfile) {
          if (!timedOut) {
            clearTimeout(timeoutId);
            setSessionPhase('child');
            setResult('authenticated');
            setLoading(false);
          }
          return;
        }

        const restored = await authService.restoreSession();
        if (!mounted || timedOut) {
          return;
        }

        if (restored) {
          const avatarConfig = await parentAvatarStorage.get(restored.idUsuario);
          setUser(restored);
          useAppStore.getState().setAvatarConfig(avatarConfig);
        }

        await hydrateChild();
        if (!mounted || timedOut) {
          return;
        }

        const activeChild = useChildSessionStore.getState().activeChild;

        let nextResult: BootstrapResult = 'welcome';
        if (activeChild && restored) {
          setSessionPhase('child');
          nextResult = 'authenticated';
        } else if (restored) {
          setSessionPhase('parent');
          nextResult = 'authenticated';
        } else {
          const onboardingDone = await authService.hasOnboardingCompleted();
          if (!mounted) {
            return;
          }
          setSessionPhase('unauthenticated');
          nextResult = onboardingDone ? 'welcome' : 'onboarding';
        }

        if (mounted && !timedOut) {
          clearTimeout(timeoutId);
          setResult(nextResult);
          setLoading(false);
        }
      } catch {
        if (mounted && !timedOut) {
          clearTimeout(timeoutId);
          setSessionPhase('unauthenticated');
          setResult('welcome');
          setLoading(false);
        }
      }
    }

    void bootstrap();

    return () => {
      mounted = false;
      clearTimeout(timeoutId);
    };
  }, [hydrateChild, setSessionPhase, setUser]);

  return { loading, result };
}
