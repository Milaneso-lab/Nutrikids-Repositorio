import React from 'react';
import { NavigationContainer, Theme as NavigationTheme } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

import { theme, getParentTheme, getKidTheme } from '@core/theme';
import { useAppStore } from '@state/stores/appStore';
import { useThemeStore } from '@state/stores/themeStore';

import { AuthRootScreen } from './AuthRootScreen';
import { ChildRootScreen } from './ChildRootScreen';
import { FamilyRootScreen } from './FamilyRootScreen';
import { linking } from './linking';
import { RootStackParamList } from './types';

const Stack = createNativeStackNavigator<RootStackParamList>();

function buildParentNavigationTheme(colorScheme: 'light' | 'dark'): NavigationTheme {
  const parentColors = getParentTheme(colorScheme);
  return {
    dark: colorScheme === 'dark',
    colors: {
      primary: parentColors.accent,
      background: parentColors.background,
      card: parentColors.surface,
      text: parentColors.textPrimary,
      border: parentColors.border,
      notification: theme.colors.secondary[500],
    },
    fonts: {
      regular: { fontFamily: theme.fonts.regular, fontWeight: '400' },
      medium: { fontFamily: theme.fonts.medium, fontWeight: '500' },
      bold: { fontFamily: theme.fonts.bold, fontWeight: '700' },
      heavy: { fontFamily: theme.fonts.extraBold, fontWeight: '800' },
    },
  };
}

function buildKidNavigationTheme(colorScheme: 'light' | 'dark'): NavigationTheme {
  const kidColors = getKidTheme(colorScheme).colors;
  return {
    dark: colorScheme === 'dark',
    colors: {
      primary: kidColors.grape,
      background: kidColors.surface,
      card: kidColors.headerBackground,
      text: kidColors.headerText,
      border: kidColors.border,
      notification: theme.colors.secondary[500],
    },
    fonts: {
      regular: { fontFamily: theme.fonts.regular, fontWeight: '400' },
      medium: { fontFamily: theme.fonts.medium, fontWeight: '500' },
      bold: { fontFamily: theme.fonts.bold, fontWeight: '700' },
      heavy: { fontFamily: theme.fonts.extraBold, fontWeight: '800' },
    },
  };
}

const authNavigationTheme: NavigationTheme = {
  dark: false,
  colors: {
    primary: theme.colors.primary[500],
    background: theme.semantic.background,
    card: theme.semantic.surface,
    text: theme.semantic.textPrimary,
    border: theme.semantic.border,
    notification: theme.colors.secondary[500],
  },
  fonts: {
    regular: { fontFamily: theme.fonts.regular, fontWeight: '400' },
    medium: { fontFamily: theme.fonts.medium, fontWeight: '500' },
    bold: { fontFamily: theme.fonts.bold, fontWeight: '700' },
    heavy: { fontFamily: theme.fonts.extraBold, fontWeight: '800' },
  },
};

export function RootNavigator(): React.JSX.Element {
  const sessionPhase = useAppStore((state) => state.sessionPhase);
  const colorScheme = useThemeStore((state) => state.colorScheme);
  const isParent = sessionPhase === 'parent';
  const isChild = sessionPhase === 'child';

  const navigationTheme = isParent
    ? buildParentNavigationTheme(colorScheme)
    : isChild
      ? buildKidNavigationTheme(colorScheme)
      : authNavigationTheme;

  return (
    <NavigationContainer theme={navigationTheme} linking={linking}>
      <Stack.Navigator
        key={sessionPhase}
        screenOptions={{ headerShown: false }}
      >
        {isParent ? (
          <Stack.Screen name="Family" component={FamilyRootScreen} />
        ) : isChild ? (
          <Stack.Screen name="Child" component={ChildRootScreen} />
        ) : (
          <Stack.Screen name="Auth" component={AuthRootScreen} />
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
}
