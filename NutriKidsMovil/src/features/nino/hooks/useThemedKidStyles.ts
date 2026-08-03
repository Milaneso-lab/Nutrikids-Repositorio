import { useMemo } from 'react';
import { StyleSheet } from 'react-native';

import type { KidTheme } from '../theme/kidTheme';
import { useKidTheme } from '../providers/KidThemeProvider';

export function useThemedKidStyles<T extends StyleSheet.NamedStyles<T>>(
  factory: (theme: KidTheme) => T,
): T {
  const { theme } = useKidTheme();
  return useMemo(() => StyleSheet.create(factory(theme)), [theme]);
}
