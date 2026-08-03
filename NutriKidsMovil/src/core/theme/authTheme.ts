import { palette } from './colors';

export type AuthColorScheme = 'light' | 'dark';

export interface AuthThemePalette {
  background: string;
  backgroundGradient: readonly [string, string, string];
  surface: string;
  textPrimary: string;
  textSecondary: string;
  accent: string;
  accentSoft: string;
  accentMuted: string;
  border: string;
  statusBar: 'light' | 'dark';
}

const lightAuthTheme: AuthThemePalette = {
  background: '#FAFAFA',
  backgroundGradient: [palette.primary[50], '#FAFAFA', '#FFFDF8'],
  surface: '#FFFFFF',
  textPrimary: palette.primary[700],
  textSecondary: '#475569',
  accent: palette.primary[700],
  accentSoft: palette.primary[50],
  accentMuted: palette.primary[100],
  border: '#D5E3DA',
  statusBar: 'dark',
};

const darkAuthTheme: AuthThemePalette = {
  background: '#0B1220',
  backgroundGradient: ['#0B1220', '#111827', '#0F172A'],
  surface: '#1A2744',
  textPrimary: '#FFFFFF',
  textSecondary: '#E2E8F0',
  accent: '#6EE7A8',
  accentSoft: '#123828',
  accentMuted: '#1C4D38',
  border: '#3B4D66',
  statusBar: 'light',
};

export function getAuthTheme(scheme: AuthColorScheme): AuthThemePalette {
  return scheme === 'dark' ? darkAuthTheme : lightAuthTheme;
}
