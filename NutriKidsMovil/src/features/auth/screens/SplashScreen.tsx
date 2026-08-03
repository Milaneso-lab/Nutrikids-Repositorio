import React, { useEffect, useRef } from 'react';
import { ActivityIndicator, Animated, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { APP_NAME } from '@core/config/constants';
import { AuthLogo } from '@features/auth/components/AuthLogo';
import { AuthText } from '@features/auth/components/AuthText';
import { useAuthBootstrap } from '@features/auth/hooks/useAuthBootstrap';
import { useAuthTheme } from '@features/auth/providers/AuthThemeProvider';
import { AuthStackParamList } from '@navigation/types';
import { SafeScreen } from '@shared/components/layout/SafeScreen';

type Props = NativeStackScreenProps<AuthStackParamList, 'Splash'>;

export function SplashScreen({ navigation }: Props): React.JSX.Element {
  const { colors } = useAuthTheme();
  const { loading, result } = useAuthBootstrap();
  const fade = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    Animated.timing(fade, {
      toValue: 1,
      duration: 700,
      useNativeDriver: true,
    }).start();
  }, [fade]);

  useEffect(() => {
    if (loading || !result) {
      return;
    }

    if (result === 'authenticated') {
      return;
    }

    const timer = setTimeout(() => {
      if (result === 'onboarding') {
        navigation.replace('Onboarding');
        return;
      }
      navigation.replace('Welcome');
    }, 400);

    return () => clearTimeout(timer);
  }, [loading, navigation, result]);

  return (
    <SafeScreen backgroundColor={colors.background}>
      <Animated.View style={[styles.container, { opacity: fade, backgroundColor: colors.background }]} accessibilityLabel={`Cargando ${APP_NAME}`}>
        <AuthLogo subtitle="Hábitos saludables en forma de juego" />
        <ActivityIndicator size="large" color={colors.accent} accessibilityLabel="Cargando" />
        <AuthText variant="bodySmall" tone="secondary">
          Verificando tu sesión…
        </AuthText>
      </Animated.View>
    </SafeScreen>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
    gap: 24,
  },
});
