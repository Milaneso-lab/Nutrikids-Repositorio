import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { StatusBar } from 'expo-status-bar';

import { ForgotPasswordScreen } from '@features/auth/screens/ForgotPasswordScreen';
import { ChildLoginScreen } from '@features/auth/screens/ChildLoginScreen';
import { LoginScreen } from '@features/auth/screens/LoginScreen';
import { OnboardingScreen } from '@features/auth/screens/OnboardingScreen';
import { RegisterScreen } from '@features/auth/screens/RegisterScreen';
import { ResetPasswordScreen } from '@features/auth/screens/ResetPasswordScreen';
import { SplashScreen } from '@features/auth/screens/SplashScreen';
import { WelcomeScreen } from '@features/auth/screens/WelcomeScreen';
import { useAuthTheme } from '@features/auth/providers/AuthThemeProvider';

import { AuthStackParamList } from './types';

const Stack = createNativeStackNavigator<AuthStackParamList>();

export function AuthNavigator(): React.JSX.Element {
  const { colors, isDark } = useAuthTheme();

  return (
    <>
      <StatusBar style={isDark ? 'light' : 'dark'} />
      <Stack.Navigator
        screenOptions={{
          headerShown: false,
          animation: 'slide_from_right',
          contentStyle: { backgroundColor: colors.background },
        }}
        initialRouteName="Splash"
      >
        <Stack.Screen name="Splash" component={SplashScreen} options={{ animation: 'fade' }} />
        <Stack.Screen name="Onboarding" component={OnboardingScreen} options={{ animation: 'fade_from_bottom' }} />
        <Stack.Screen name="Welcome" component={WelcomeScreen} options={{ animation: 'fade' }} />
        <Stack.Screen name="Login" component={LoginScreen} />
        <Stack.Screen name="ChildLogin" component={ChildLoginScreen} />
        <Stack.Screen name="Register" component={RegisterScreen} />
        <Stack.Screen name="ForgotPassword" component={ForgotPasswordScreen} />
        <Stack.Screen name="ResetPassword" component={ResetPasswordScreen} />
      </Stack.Navigator>
    </>
  );
}
