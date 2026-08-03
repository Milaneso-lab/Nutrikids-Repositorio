import React, { createContext, useContext, useMemo } from 'react';

import { getParentTheme, type ParentThemePalette } from '@core/theme/parentTheme';
import { useThemeStore } from '@state/stores/themeStore';

interface ParentThemeContextValue {
  colors: ParentThemePalette;
  isDark: boolean;
}

const ParentThemeContext = createContext<ParentThemeContextValue | null>(null);

export function ParentThemeProvider({ children }: { children: React.ReactNode }): React.JSX.Element {
  const colorScheme = useThemeStore((state) => state.colorScheme);

  const value = useMemo(
    () => ({
      colors: getParentTheme(colorScheme),
      isDark: colorScheme === 'dark',
    }),
    [colorScheme],
  );

  return <ParentThemeContext.Provider value={value}>{children}</ParentThemeContext.Provider>;
}

export function useParentTheme(): ParentThemeContextValue {
  const context = useContext(ParentThemeContext);
  if (!context) {
    return {
      colors: getParentTheme('light'),
      isDark: false,
    };
  }
  return context;
}
