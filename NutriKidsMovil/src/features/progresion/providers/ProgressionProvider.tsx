import React from 'react';

import { useChildSessionStore } from '@features/nino/store/childSessionStore';

import { ProgressionCelebrationOverlay } from '../components/ProgressionCelebrationOverlay';
import { useProgression, useProgressionBootstrap } from '../hooks/useProgression';

interface ProgressionProviderProps {
  children: React.ReactNode;
}

export function ProgressionProvider({ children }: ProgressionProviderProps): React.JSX.Element {
  const sessionPhase = useChildSessionStore((s) => s.activeChild);
  useProgressionBootstrap();
  const { celebrations, dequeueCelebration } = useProgression();

  const activeCelebration = sessionPhase ? celebrations[0] ?? null : null;

  return (
    <>
      {children}
      <ProgressionCelebrationOverlay item={activeCelebration} onDismiss={dequeueCelebration} />
    </>
  );
}
