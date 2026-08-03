import { useAppStore } from '@state/stores/appStore';
import { useThemeStore } from '@state/stores/themeStore';
import { getAuthTheme } from '@core/theme/authTheme';
import { getParentTheme } from '@core/theme/parentTheme';

export interface ButtonThemeColors {
  accent: string;
  accentSoft: string;
  accentMuted: string;
  textInverse: string;
  ghostLabel: string;
}

export function useButtonThemeColors(): ButtonThemeColors {
  const sessionPhase = useAppStore((state) => state.sessionPhase);
  const colorScheme = useThemeStore((state) => state.colorScheme);

  if (sessionPhase === 'parent') {
    const colors = getParentTheme(colorScheme);
    return {
      accent: colors.accent,
      accentSoft: colors.accentSoft,
      accentMuted: colors.accentMuted,
      textInverse: colors.textInverse,
      ghostLabel: colors.accent,
    };
  }

  const auth = getAuthTheme(colorScheme);
  return {
    accent: auth.accent,
    accentSoft: auth.accentSoft,
    accentMuted: auth.accentMuted,
    textInverse: colorScheme === 'dark' ? '#0B1220' : '#FFFFFF',
    ghostLabel: auth.accent,
  };
}
