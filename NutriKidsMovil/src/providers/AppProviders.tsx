import React, { useCallback, useEffect } from 'react';
import { ActivityIndicator, StyleSheet, View } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import {
  Nunito_400Regular,
  Nunito_500Medium,
  Nunito_600SemiBold,
  Nunito_700Bold,
  Nunito_800ExtraBold,
  useFonts,
} from '@expo-google-fonts/nunito';
import * as SplashScreen from 'expo-splash-screen';
import { SafeAreaProvider } from 'react-native-safe-area-context';

import { configureAuthHandlers } from '@core/api';
import { ErrorBoundary } from '@core/errors/ErrorBoundary';
import { ProgressionProvider } from '@features/progresion/providers/ProgressionProvider';
import { CommunicationProvider } from '@features/comunicacion/providers/CommunicationProvider';
import { RootNavigator } from '@navigation/RootNavigator';
import { theme } from '@core/theme';
import { authService } from '@services/auth';
import { childAuthService } from '@services/auth/childAuthService';
import { ApiConnectionBanner } from '@shared/components/layout/ApiConnectionBanner';
import { LoadingOverlay } from '@shared/components/ui/LoadingOverlay';
import { useAppStore } from '@state/stores/appStore';
import { useThemeStore } from '@state/stores/themeStore';
import { useUiStore } from '@state/stores/uiStore';

SplashScreen.preventAutoHideAsync().catch(() => undefined);

const STARTUP_TIMEOUT_MS = 5_000;

export function AppProviders(): React.JSX.Element {
  const [fontsLoaded, fontError] = useFonts({
    Nunito_400Regular,
    Nunito_500Medium,
    Nunito_600SemiBold,
    Nunito_700Bold,
    Nunito_800ExtraBold,
  });

  const signOut = useAppStore((state) => state.signOut);
  const hydrateTheme = useThemeStore((state) => state.hydrate);
  const globalLoading = useUiStore((state) => state.globalLoading);
  const setReady = useAppStore((state) => state.setReady);
  const isReady = useAppStore((state) => state.isReady);

  const handleUnauthorized = useCallback(async () => {
    const phase = useAppStore.getState().sessionPhase;
    if (phase === 'child') {
      await childAuthService.logout();
      return;
    }
    await authService.clearSession();
    signOut();
  }, [signOut]);

  useEffect(() => {
    void hydrateTheme();
  }, [hydrateTheme]);

  useEffect(() => {
    configureAuthHandlers({
      getAccessToken: authService.getAccessToken,
      onUnauthorized: handleUnauthorized,
    });
  }, [handleUnauthorized]);

  useEffect(() => {
    let cancelled = false;

    const finishStartup = (): void => {
      if (cancelled) {
        return;
      }
      setReady(true);
      void SplashScreen.hideAsync();
    };

    if (fontsLoaded || fontError) {
      finishStartup();
    }

    const timeoutId = setTimeout(finishStartup, STARTUP_TIMEOUT_MS);

    return () => {
      cancelled = true;
      clearTimeout(timeoutId);
    };
  }, [fontError, fontsLoaded, setReady]);

  if (!isReady) {
    return (
      <View style={styles.initialLoading}>
        <ActivityIndicator size="large" color={theme.colors.primary[500]} />
      </View>
    );
  }

  return (
    <SafeAreaProvider>
      <ErrorBoundary>
        <StatusBar style="dark" />
        <ApiConnectionBanner />
        <ProgressionProvider>
          <CommunicationProvider>
            <RootNavigator />
          </CommunicationProvider>
        </ProgressionProvider>
        <LoadingOverlay visible={globalLoading} />
      </ErrorBoundary>
    </SafeAreaProvider>
  );
}

const styles = StyleSheet.create({
  initialLoading: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FFFFFF',
  },
});
