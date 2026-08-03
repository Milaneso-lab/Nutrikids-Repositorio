import { theme } from '@core/theme';

export type KidColorScheme = 'light' | 'dark';

/** Acentos lúdicos — compartidos en ambos modos. */
export const kidAccents = {
  sky: '#7DD3FC',
  sunshine: '#FDE047',
  coral: '#FB7185',
  mint: '#6EE7B7',
  lavender: '#C4B5FD',
  bubblegum: '#F9A8D4',
  ocean: '#5EEAD4',
  grape: '#A78BFA',
  peach: '#FDBA74',
  grass: '#86EFAC',
} as const;

export interface KidThemeColors {
  sky: string;
  sunshine: string;
  coral: string;
  mint: string;
  lavender: string;
  bubblegum: string;
  ocean: string;
  grape: string;
  peach: string;
  grass: string;
  /** Superficie de tarjetas y paneles */
  white: string;
  /** Texto principal sobre tarjetas */
  ink: string;
  /** Texto secundario sobre tarjetas */
  inkSoft: string;
  surface: string;
  surfaceElevated: string;
  surfaceMuted: string;
  textOnGradient: string;
  textOnGradientMuted: string;
  border: string;
  tabBar: string;
  headerBackground: string;
  headerText: string;
  headerTint: string;
  menuPressed: string;
  progressTrack: string;
  shadow: string;
  inputBackground: string;
  inputBorder: string;
  inputText: string;
  inputPlaceholder: string;
}

export interface KidThemeGradients {
  home: readonly [string, string];
  profile: readonly [string, string];
  adventure: readonly [string, string];
  more: readonly [string, string];
  comingSoon: readonly [string, string];
}

export interface KidThemeShadow {
  card: {
    shadowColor: string;
    shadowOffset: { width: number; height: number };
    shadowOpacity: number;
    shadowRadius: number;
    elevation: number;
  };
  button: {
    shadowColor: string;
    shadowOffset: { width: number; height: number };
    shadowOpacity: number;
    shadowRadius: number;
    elevation: number;
  };
}

export interface KidTheme {
  colors: KidThemeColors;
  gradients: KidThemeGradients;
  radii: typeof kidRadii;
  shadow: KidThemeShadow;
  fonts: typeof theme.fonts;
  spacing: typeof theme.spacing;
}

export const kidRadii = {
  card: 24,
  button: 20,
  pill: 999,
  blob: 32,
} as const;

const kidShadowLight = {
  card: {
    shadowColor: kidAccents.grape,
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.18,
    shadowRadius: 12,
    elevation: 6,
  },
  button: {
    shadowColor: kidAccents.coral,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 4,
  },
} as const;

const kidShadowDark = {
  card: {
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.35,
    shadowRadius: 12,
    elevation: 6,
  },
  button: {
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
  },
} as const;

const lightGradients: KidThemeGradients = {
  home: ['#FFE259', '#FFA751'],
  profile: ['#C4B5FD', '#F9A8D4'],
  adventure: ['#6EE7B7', '#7DD3FC'],
  more: ['#FDE047', '#FDBA74'],
  comingSoon: ['#E0E7FF', '#FCE7F3'],
};

const darkGradients: KidThemeGradients = {
  home: ['#1E3A5F', '#0F766E'],
  profile: ['#4C1D95', '#831843'],
  adventure: ['#065F46', '#0E7490'],
  more: ['#78350F', '#B45309'],
  comingSoon: ['#1E293B', '#312E81'],
};

const lightKidTheme: KidTheme = {
  colors: {
    ...kidAccents,
    white: '#FFFFFF',
    ink: '#1E293B',
    inkSoft: '#475569',
    surface: '#FFFFFF',
    surfaceElevated: '#FFFFFF',
    surfaceMuted: '#F8FAFC',
    textOnGradient: '#FFFFFF',
    textOnGradientMuted: 'rgba(255,255,255,0.9)',
    border: '#E2E8F0',
    tabBar: '#FFFFFF',
    headerBackground: '#FFFFFF',
    headerText: '#1E293B',
    headerTint: kidAccents.grape,
    menuPressed: 'rgba(196, 181, 253, 0.35)',
    progressTrack: '#F1F5F9',
    shadow: kidAccents.grape,
    inputBackground: '#FFFFFF',
    inputBorder: '#CBD5E1',
    inputText: '#1E293B',
    inputPlaceholder: '#64748B',
  },
  gradients: lightGradients,
  radii: kidRadii,
  shadow: kidShadowLight,
  fonts: theme.fonts,
  spacing: theme.spacing,
};

const darkKidTheme: KidTheme = {
  colors: {
    ...kidAccents,
    white: '#1A2744',
    ink: '#FFFFFF',
    inkSoft: '#E2E8F0',
    surface: '#1A2744',
    surfaceElevated: '#243352',
    surfaceMuted: '#152238',
    textOnGradient: '#FFFFFF',
    textOnGradientMuted: 'rgba(255,255,255,0.95)',
    border: '#3B4D66',
    tabBar: '#111827',
    headerBackground: '#1A2744',
    headerText: '#F1F5F9',
    headerTint: kidAccents.mint,
    menuPressed: 'rgba(110, 231, 183, 0.15)',
    progressTrack: '#334155',
    shadow: '#000000',
    inputBackground: '#111827',
    inputBorder: '#475569',
    inputText: '#F8FAFC',
    inputPlaceholder: '#94A3B8',
  },
  gradients: darkGradients,
  radii: kidRadii,
  shadow: kidShadowDark,
  fonts: theme.fonts,
  spacing: theme.spacing,
};

export function getKidTheme(scheme: KidColorScheme): KidTheme {
  return scheme === 'dark' ? darkKidTheme : lightKidTheme;
}

/** Tema claro por defecto — preferir `useKidTheme()` en componentes interactivos. */
export const kidTheme = lightKidTheme;

export type KidThemeType = KidTheme;
