import React, { createContext, useContext, useMemo } from 'react';

import { getKidTheme, type KidTheme } from '../theme/kidTheme';
import { useThemeStore } from '@state/stores/themeStore';

interface KidThemeContextValue {
  theme: KidTheme;
  colors: KidTheme['colors'];
  gradients: KidTheme['gradients'];
  isDark: boolean;
}

const KidThemeContext = createContext<KidThemeContextValue | null>(null);

export function KidThemeProvider({ children }: { children: React.ReactNode }): React.JSX.Element {
  const colorScheme = useThemeStore((state) => state.colorScheme);

  const value = useMemo(() => {
    const theme = getKidTheme(colorScheme);
    return {
      theme,
      colors: theme.colors,
      gradients: theme.gradients,
      isDark: colorScheme === 'dark',
    };
  }, [colorScheme]);

  return <KidThemeContext.Provider value={value}>{children}</KidThemeContext.Provider>;
}

export function useKidTheme(): KidThemeContextValue {
  const context = useContext(KidThemeContext);
  if (!context) {
    const theme = getKidTheme('light');
    return {
      theme,
      colors: theme.colors,
      gradients: theme.gradients,
      isDark: false,
    };
  }
  return context;
}
