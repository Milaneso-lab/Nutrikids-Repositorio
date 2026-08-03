import { palette, semanticColors } from './colors';
import { radii, spacing } from './spacing';
import { shadows } from './shadows';
import { fontFamilies, typography } from './typography';

export const theme = {
  colors: palette,
  semantic: semanticColors,
  spacing,
  radii,
  shadows,
  fonts: fontFamilies,
  typography,
} as const;

export type AppTheme = typeof theme;
