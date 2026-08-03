import React from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { StatusBar } from 'expo-status-bar';

import { theme } from '@core/theme';
import { AuthLayout } from '@features/auth/components/AuthLayout';
import { AuthLogo } from '@features/auth/components/AuthLogo';
import { useAuthTheme } from '@features/auth/providers/AuthThemeProvider';
import { AuthStackParamList } from '@navigation/types';
import { Button } from '@shared/components/ui/Button';
import { AppText } from '@shared/components/ui/Text';
import { useThemeStore } from '@state/stores/themeStore';

type Props = NativeStackScreenProps<AuthStackParamList, 'Welcome'>;

export function WelcomeScreen({ navigation }: Props): React.JSX.Element {
  const { colors, isDark } = useAuthTheme();
  const colorScheme = useThemeStore((state) => state.colorScheme);
  const toggleColorScheme = useThemeStore((state) => state.toggleColorScheme);

  return (
    <AuthLayout scrollable={false}>
      <StatusBar style={isDark ? 'light' : 'dark'} />
      <Pressable
        accessibilityRole="button"
        accessibilityLabel={colorScheme === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'}
        onPress={() => void toggleColorScheme()}
        style={[styles.themeToggle, { backgroundColor: colors.surface, borderColor: colors.border }]}
      >
        <AppText variant="body">{colorScheme === 'dark' ? '☀️' : '🌙'}</AppText>
      </Pressable>

      <View style={styles.hero}>
        <AuthLogo subtitle="Nutrición infantil con enfoque positivo y seguro" />
      </View>

      <View style={styles.actions}>
        <Button
          label="Iniciar sesión"
          onPress={() => navigation.navigate('Login')}
          fullWidth
          accessibilityHint="Ir a la pantalla de inicio de sesión"
        />
        <Button
          label="Crear cuenta"
          variant="secondary"
          onPress={() => navigation.navigate('Register')}
          fullWidth
          accessibilityHint="Registrar una cuenta de padre"
        />
        <Button
          label="Soy un niño"
          variant="secondary"
          onPress={() => navigation.navigate('ChildLogin')}
          fullWidth
          accessibilityHint="Entrar con código de vinculación y PIN"
        />
        <Button
          label="Recuperar contraseña"
          variant="ghost"
          onPress={() => navigation.navigate('ForgotPassword')}
          fullWidth
        />
      </View>

      <AppText variant="caption" style={[styles.note, { color: colors.textSecondary }]}>
        Los padres crean la cuenta. Los niños entran con el código de vinculación y su PIN.
      </AppText>
    </AuthLayout>
  );
}

const styles = StyleSheet.create({
  themeToggle: {
    position: 'absolute',
    top: theme.spacing.sm,
    right: theme.spacing.lg,
    width: 44,
    height: 44,
    borderRadius: theme.radii.pill,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    zIndex: 2,
  },
  hero: {
    flex: 1,
    justifyContent: 'center',
  },
  actions: {
    gap: theme.spacing.sm,
  },
  note: {
    textAlign: 'center',
    marginTop: theme.spacing.md,
  },
});
