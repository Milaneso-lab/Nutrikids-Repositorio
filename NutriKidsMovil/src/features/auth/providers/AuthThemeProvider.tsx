import React, { createContext, useContext, useMemo } from 'react';

import { getAuthTheme, type AuthThemePalette } from '@core/theme/authTheme';
import { useThemeStore } from '@state/stores/themeStore';

interface AuthThemeContextValue {
  colors: AuthThemePalette;
  isDark: boolean;
}

const AuthThemeContext = createContext<AuthThemeContextValue | null>(null);

export function AuthThemeProvider({ children }: { children: React.ReactNode }): React.JSX.Element {
  const colorScheme = useThemeStore((state) => state.colorScheme);

  const value = useMemo(
    () => ({
      colors: getAuthTheme(colorScheme),
      isDark: colorScheme === 'dark',
    }),
    [colorScheme],
  );

  return <AuthThemeContext.Provider value={value}>{children}</AuthThemeContext.Provider>;
}

export function useAuthTheme(): AuthThemeContextValue {
  const context = useContext(AuthThemeContext);
  if (!context) {
    return { colors: getAuthTheme('light'), isDark: false };
  }
  return context;
}
