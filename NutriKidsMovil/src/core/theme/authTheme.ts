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
  inputBackground: string;
  inputBorder: string;
  inputText: string;
  inputPlaceholder: string;
  loginCardSurface: string;
  loginCardBorder: string;
  loginHeroText: string;
  loginHeroSubtext: string;
  statusBar: 'light' | 'dark';
}

const lightAuthTheme: AuthThemePalette = {
  background: '#FAFAFA',
  backgroundGradient: [palette.primary[50], '#FAFAFA', '#FFFDF8'],
  surface: '#FFFFFF',
  textPrimary: palette.primary[700],
  textSecondary: '#334155',
  accent: palette.primary[700],
  accentSoft: palette.primary[50],
  accentMuted: palette.primary[100],
  border: '#94A3B8',
  inputBackground: '#FFFFFF',
  inputBorder: '#64748B',
  inputText: '#0F172A',
  inputPlaceholder: '#64748B',
  loginCardSurface: '#FFFFFF',
  loginCardBorder: 'rgba(255,255,255,0.65)',
  loginHeroText: '#FFFFFF',
  loginHeroSubtext: 'rgba(255,255,255,0.92)',
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
  border: '#64748B',
  inputBackground: '#0F172A',
  inputBorder: '#94A3B8',
  inputText: '#F8FAFC',
  inputPlaceholder: '#94A3B8',
  loginCardSurface: '#1E293B',
  loginCardBorder: 'rgba(148,163,184,0.35)',
  loginHeroText: '#FFFFFF',
  loginHeroSubtext: 'rgba(226,232,240,0.92)',
  statusBar: 'light',
};

export function getAuthTheme(scheme: AuthColorScheme): AuthThemePalette {
  return scheme === 'dark' ? darkAuthTheme : lightAuthTheme;
}
