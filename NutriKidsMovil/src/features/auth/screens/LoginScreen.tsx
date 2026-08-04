import React, { useMemo, useState } from 'react';
import {
  ActivityIndicator,
  Image,
  Pressable,
  StyleSheet,
  View,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { APP_NAME } from '@core/config/constants';
import { theme } from '@core/theme';
import { FormErrorBanner } from '@features/auth/components/FormErrorBanner';
import { LoginScreenBackground } from '@features/auth/components/LoginScreenBackground';
import { PasswordInputField, TextInputField } from '@features/auth/components/TextInputField';
import { useAuth } from '@features/auth/hooks/useAuth';
import { useAuthTheme } from '@features/auth/providers/AuthThemeProvider';
import { validateLoginForm } from '@features/auth/validation/authValidation';
import { AuthStackParamList } from '@navigation/types';
import { Button } from '@shared/components/ui/Button';
import { SafeScreen } from '@shared/components/layout/SafeScreen';
import { KeyboardAwareScroll } from '@shared/components/layout/KeyboardAwareScroll';
import { AppText } from '@shared/components/ui/Text';
import { useNetworkStatus } from '@shared/hooks/useNetworkStatus';
import { useThemeStore } from '@state/stores/themeStore';

type Props = NativeStackScreenProps<AuthStackParamList, 'Login'>;

export function LoginScreen({ navigation }: Props): React.JSX.Element {
  const { login, loading, error, clearError } = useAuth();
  const { isConnected } = useNetworkStatus();
  const { colors, isDark } = useAuthTheme();
  const colorScheme = useThemeStore((state) => state.colorScheme);
  const toggleColorScheme = useThemeStore((state) => state.toggleColorScheme);
  const [email, setEmail] = useState('');
  const [contrasena, setContrasena] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  const cardGradient = useMemo(
    () =>
      isDark
        ? (['rgba(30,41,59,0.98)', 'rgba(15,23,42,0.96)'] as const)
        : (['rgba(255,255,255,0.97)', 'rgba(255,253,248,0.94)'] as const),
    [isDark],
  );

  const logoGradient = useMemo(
    () => (isDark ? (['#1E293B', '#0F172A'] as const) : (['#FFFFFF', '#F1F8E9'] as const)),
    [isDark],
  );

  const handleSubmit = async (): Promise<void> => {
    clearError();
    const validation = validateLoginForm({ email, contrasena });
    setFieldErrors(validation.errors);
    if (!validation.valid) {
      return;
    }
    if (!isConnected) {
      return;
    }
    await login(email, contrasena);
  };

  return (
    <SafeScreen edges={['top', 'bottom']}>
      <View style={styles.root}>
        <LoginScreenBackground />

        <Pressable
          accessibilityRole="button"
          accessibilityLabel={colorScheme === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'}
          onPress={() => void toggleColorScheme()}
          style={[
            styles.themeToggle,
            {
              backgroundColor: isDark ? 'rgba(30,41,59,0.85)' : 'rgba(255,255,255,0.35)',
              borderColor: isDark ? colors.loginCardBorder : 'rgba(255,255,255,0.55)',
            },
          ]}
        >
          <AppText variant="body">{colorScheme === 'dark' ? '☀️' : '🌙'}</AppText>
        </Pressable>

        <KeyboardAwareScroll contentContainerStyle={styles.scrollContent}>
          <View style={styles.hero}>
            <View
              style={[
                styles.logoRing,
                {
                  backgroundColor: isDark ? 'rgba(15,23,42,0.55)' : 'rgba(255,255,255,0.45)',
                },
              ]}
            >
              <LinearGradient colors={[...logoGradient]} style={styles.logoGradient}>
                <Image
                  source={require('../../../../assets/nutrikids-logo.png')}
                  style={styles.logo}
                  resizeMode="contain"
                  accessibilityLabel={`Logo ${APP_NAME}`}
                />
              </LinearGradient>
            </View>
            <View
              style={[
                styles.badge,
                {
                  backgroundColor: isDark ? 'rgba(15,23,42,0.45)' : 'rgba(255,255,255,0.28)',
                  borderColor: isDark ? colors.loginCardBorder : 'rgba(255,255,255,0.45)',
                },
              ]}
            >
              <AppText variant="caption" style={[styles.badgeText, { color: colors.loginHeroText }]}>
                ✨ Bienvenido de nuevo
              </AppText>
            </View>
            <AppText variant="h1" style={[styles.title, { color: colors.loginHeroText }]}>
              Iniciar sesión
            </AppText>
            <AppText variant="body" style={[styles.subtitle, { color: colors.loginHeroSubtext }]}>
              Accede con tu cuenta de padre para supervisar el progreso de tus hijos.
            </AppText>
          </View>

          <View
            style={[
              styles.card,
              {
                borderColor: colors.loginCardBorder,
                backgroundColor: colors.loginCardSurface,
              },
            ]}
          >
            <LinearGradient colors={[...cardGradient]} style={styles.cardGradient}>
              {!isConnected ? (
                <View style={styles.alertWrap}>
                  <FormErrorBanner message="Sin conexión a internet. Conéctate para iniciar sesión." />
                </View>
              ) : null}
              {error ? (
                <View style={styles.alertWrap}>
                  <FormErrorBanner message={error} />
                </View>
              ) : null}

              <View style={styles.form}>
                <TextInputField
                  label="Correo electrónico"
                  value={email}
                  onChangeText={setEmail}
                  keyboardType="email-address"
                  autoCapitalize="none"
                  autoCorrect={false}
                  textContentType="emailAddress"
                  error={fieldErrors.email}
                />
                <PasswordInputField
                  label="Contraseña"
                  value={contrasena}
                  onChangeText={setContrasena}
                  error={fieldErrors.contrasena}
                />
              </View>

              <Pressable
                accessibilityRole="button"
                accessibilityState={{ disabled: loading, busy: loading }}
                disabled={loading}
                onPress={() => void handleSubmit()}
                style={({ pressed }) => [styles.primaryWrap, pressed && !loading ? styles.primaryPressed : null]}
              >
                <LinearGradient
                  colors={['#2E7D32', '#43A047', '#66BB6A']}
                  start={{ x: 0, y: 0.5 }}
                  end={{ x: 1, y: 0.5 }}
                  style={styles.primaryButton}
                >
                  {loading ? (
                    <ActivityIndicator color="#FFFFFF" />
                  ) : (
                    <AppText variant="button" style={styles.primaryLabel}>
                      Entrar
                    </AppText>
                  )}
                </LinearGradient>
              </Pressable>

              <Button
                label="Soy un niño — entrar con mi PIN"
                variant="secondary"
                onPress={() => navigation.navigate('ChildLogin')}
                fullWidth
                style={
                  isDark
                    ? { backgroundColor: colors.accentSoft, borderColor: colors.accent, borderWidth: 1.5 }
                    : styles.childButton
                }
              />
              <Button
                label="¿Olvidaste tu contraseña?"
                variant="ghost"
                onPress={() => navigation.navigate('ForgotPassword')}
                style={
                  isDark
                    ? { backgroundColor: 'rgba(15,23,42,0.55)' }
                    : styles.ghostButton
                }
              />
              <Button
                label="Crear cuenta"
                variant="secondary"
                onPress={() => navigation.navigate('Register')}
                fullWidth
                style={
                  isDark
                    ? { backgroundColor: colors.accentMuted, borderColor: colors.border, borderWidth: 1.5 }
                    : styles.registerButton
                }
              />
            </LinearGradient>
          </View>
        </KeyboardAwareScroll>
      </View>
    </SafeScreen>
  );
}

const styles = StyleSheet.create({
  root: {
    flex: 1,
  },
  themeToggle: {
    position: 'absolute',
    top: theme.spacing.sm,
    right: theme.spacing.lg,
    width: 44,
    height: 44,
    borderRadius: theme.radii.pill,
    borderWidth: 1,
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 10,
  },
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: theme.spacing.lg,
    paddingTop: theme.spacing.lg,
    paddingBottom: theme.spacing.xl,
    gap: theme.spacing.lg,
  },
  hero: {
    alignItems: 'center',
    gap: theme.spacing.sm,
    paddingTop: theme.spacing.sm,
  },
  logoRing: {
    padding: 6,
    borderRadius: theme.radii.xl + 8,
    ...theme.shadows.lg,
  },
  logoGradient: {
    borderRadius: theme.radii.xl,
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: theme.spacing.xs,
    alignItems: 'center',
    justifyContent: 'center',
  },
  logo: {
    width: 168,
    height: 200,
  },
  badge: {
    marginTop: theme.spacing.xs,
    paddingHorizontal: theme.spacing.md,
    paddingVertical: theme.spacing.xxs,
    borderRadius: theme.radii.pill,
    borderWidth: 1,
  },
  badgeText: {
    fontFamily: theme.fonts.semiBold,
    letterSpacing: 0.4,
  },
  title: {
    textAlign: 'center',
    textShadowColor: 'rgba(0,0,0,0.18)',
    textShadowOffset: { width: 0, height: 2 },
    textShadowRadius: 8,
  },
  subtitle: {
    textAlign: 'center',
    maxWidth: 320,
    lineHeight: 22,
  },
  card: {
    borderRadius: theme.radii.xl,
    overflow: 'hidden',
    borderWidth: 1,
    ...theme.shadows.lg,
  },
  cardGradient: {
    padding: theme.spacing.lg,
    gap: theme.spacing.md,
  },
  alertWrap: {
    marginBottom: theme.spacing.xxs,
  },
  form: {
    gap: theme.spacing.md,
  },
  primaryWrap: {
    borderRadius: theme.radii.md,
    overflow: 'hidden',
    ...theme.shadows.md,
  },
  primaryPressed: {
    opacity: 0.92,
    transform: [{ scale: 0.99 }],
  },
  primaryButton: {
    minHeight: 52,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: theme.spacing.lg,
  },
  primaryLabel: {
    color: '#FFFFFF',
    letterSpacing: 0.3,
  },
  childButton: {
    backgroundColor: 'rgba(255, 243, 224, 0.95)',
    borderColor: theme.colors.secondary[400],
    borderWidth: 1.5,
  },
  registerButton: {
    backgroundColor: 'rgba(227, 242, 253, 0.95)',
    borderColor: theme.colors.accent[400],
    borderWidth: 1.5,
  },
  ghostButton: {
    backgroundColor: 'rgba(255,255,255,0.55)',
  },
});
