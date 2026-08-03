import { Platform, ViewStyle } from 'react-native';

import { palette } from './colors';

export const shadows = {
  sm: Platform.select<ViewStyle>({
    ios: {
      shadowColor: palette.neutral[900],
      shadowOffset: { width: 0, height: 1 },
      shadowOpacity: 0.08,
      shadowRadius: 2,
    },
    android: { elevation: 2 },
    default: {},
  }),
  md: Platform.select<ViewStyle>({
    ios: {
      shadowColor: palette.neutral[900],
      shadowOffset: { width: 0, height: 2 },
      shadowOpacity: 0.12,
      shadowRadius: 6,
    },
    android: { elevation: 4 },
    default: {},
  }),
  lg: Platform.select<ViewStyle>({
    ios: {
      shadowColor: palette.neutral[900],
      shadowOffset: { width: 0, height: 4 },
      shadowOpacity: 0.16,
      shadowRadius: 12,
    },
    android: { elevation: 8 },
    default: {},
  }),
} as const;

export type ShadowKey = keyof typeof shadows;
