import { useAppStore } from '@state/stores/appStore';
import { useThemeStore } from '@state/stores/themeStore';
import { getAuthTheme } from '@core/theme/authTheme';
import { getParentTheme } from '@core/theme/parentTheme';

export interface FormThemeColors {
  textSecondary: string;
  accent: string;
  inputBackground: string;
  inputBorder: string;
  inputText: string;
  inputPlaceholder: string;
}

export function useFormThemeColors(): FormThemeColors {
  const sessionPhase = useAppStore((state) => state.sessionPhase);
  const colorScheme = useThemeStore((state) => state.colorScheme);

  if (sessionPhase === 'parent') {
    const colors = getParentTheme(colorScheme);
    return {
      textSecondary: colors.textSecondary,
      accent: colors.accent,
      inputBackground: colors.inputBackground,
      inputBorder: colors.inputBorder,
      inputText: colors.inputText,
      inputPlaceholder: colors.inputPlaceholder,
    };
  }

  const auth = getAuthTheme(colorScheme);
  return {
    textSecondary: auth.textSecondary,
    accent: auth.accent,
    inputBackground: colorScheme === 'dark' ? '#111827' : '#FFFFFF',
    inputBorder: auth.border,
    inputText: auth.textPrimary,
    inputPlaceholder: colorScheme === 'dark' ? '#94A3B8' : '#64748B',
  };
}
