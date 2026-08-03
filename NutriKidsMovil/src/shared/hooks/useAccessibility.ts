import { useMemo } from 'react';
import { AccessibilityInfo } from 'react-native';

export interface AccessibilityPreferences {
  reduceMotionEnabled: boolean;
  screenReaderEnabled: boolean;
}

/**
 * Hook base para accesibilidad.
 * La suscripción a eventos se ampliará cuando existan pantallas finales.
 */
export function useAccessibility(): AccessibilityPreferences {
  return useMemo(
    () => ({
      reduceMotionEnabled: false,
      screenReaderEnabled: false,
    }),
    [],
  );
}

export async function fetchAccessibilityPreferences(): Promise<AccessibilityPreferences> {
  const [reduceMotionEnabled, screenReaderEnabled] = await Promise.all([
    AccessibilityInfo.isReduceMotionEnabled(),
    AccessibilityInfo.isScreenReaderEnabled(),
  ]);

  return { reduceMotionEnabled, screenReaderEnabled };
}
