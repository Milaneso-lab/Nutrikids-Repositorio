import { palette } from './colors';

export type ParentColorScheme = 'light' | 'dark';

export interface ParentThemePalette {
  background: string;
  surface: string;
  surfaceElevated: string;
  surfaceMuted: string;
  textPrimary: string;
  textSecondary: string;
  textInverse: string;
  border: string;
  accent: string;
  accentSoft: string;
  accentMuted: string;
  heroGradient: readonly [string, string];
  heroText: string;
  heroSubtext: string;
  menuPressed: string;
  inputBackground: string;
  inputBorder: string;
  inputText: string;
  inputPlaceholder: string;
  statPill: string;
  cardAccent: string;
  shadow: string;
  progressTrack: string;
  statusBar: 'light' | 'dark';
}

const lightParentTheme: ParentThemePalette = {
  background: '#EEF3F0',
  surface: '#FFFFFF',
  surfaceElevated: '#FFFFFF',
  surfaceMuted: '#E8F3EB',
  textPrimary: '#0F1F17',
  textSecondary: '#475569',
  textInverse: '#FFFFFF',
  border: '#D5E3DA',
  accent: palette.primary[700],
  accentSoft: palette.primary[50],
  accentMuted: palette.primary[100],
  heroGradient: ['#1B5E20', '#43A047'] as const,
  heroText: '#FFFFFF',
  heroSubtext: 'rgba(255,255,255,0.9)',
  menuPressed: palette.primary[50],
  inputBackground: '#FFFFFF',
  inputBorder: '#C8D9CE',
  inputText: '#0F1F17',
  inputPlaceholder: '#64748B',
  statPill: '#E8F5E9',
  cardAccent: palette.primary[600],
  shadow: '#0F1F17',
  progressTrack: '#D8E6DC',
  statusBar: 'dark',
};

const darkParentTheme: ParentThemePalette = {
  background: '#0B1220',
  surface: '#1A2744',
  surfaceElevated: '#243352',
  surfaceMuted: '#152238',
  textPrimary: '#FFFFFF',
  textSecondary: '#E2E8F0',
  textInverse: '#0B1220',
  border: '#3B4D66',
  accent: '#6EE7A8',
  accentSoft: '#123828',
  accentMuted: '#1C4D38',
  heroGradient: ['#14532D', '#166534'] as const,
  heroText: '#FFFFFF',
  heroSubtext: 'rgba(255,255,255,0.88)',
  menuPressed: '#1A2E26',
  inputBackground: '#111827',
  inputBorder: '#3B4D66',
  inputText: '#F8FAFC',
  inputPlaceholder: '#94A3B8',
  statPill: '#1A2E26',
  cardAccent: '#6EE7A8',
  shadow: '#000000',
  progressTrack: '#243044',
  statusBar: 'light',
};

export function getParentTheme(scheme: ParentColorScheme): ParentThemePalette {
  return scheme === 'dark' ? darkParentTheme : lightParentTheme;
}
