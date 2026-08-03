import React, { useState } from 'react';
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
import { validateLoginForm } from '@features/auth/validation/authValidation';
import { AuthStackParamList } from '@navigation/types';
import { Button } from '@shared/components/ui/Button';
import { SafeScreen } from '@shared/components/layout/SafeScreen';
import { KeyboardAwareScroll } from '@shared/components/layout/KeyboardAwareScroll';
import { AppText } from '@shared/components/ui/Text';
import { useNetworkStatus } from '@shared/hooks/useNetworkStatus';

type Props = NativeStackScreenProps<AuthStackParamList, 'Login'>;

export function LoginScreen({ navigation }: Props): React.JSX.Element {
  const { login, loading, error, clearError } = useAuth();
  const { isConnected } = useNetworkStatus();
  const [email, setEmail] = useState('');
  const [contrasena, setContrasena] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

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

        <KeyboardAwareScroll contentContainerStyle={styles.scrollContent}>
            <View style={styles.hero}>
              <View style={styles.logoRing}>
                <LinearGradient
                  colors={['#FFFFFF', '#F1F8E9']}
                  style={styles.logoGradient}
                >
                  <Image
                    source={require('../../../../assets/nutrikids-logo.png')}
                    style={styles.logo}
                    resizeMode="contain"
                    accessibilityLabel={`Logo ${APP_NAME}`}
                  />
                </LinearGradient>
              </View>
              <View style={styles.badge}>
                <AppText variant="caption" style={styles.badgeText}>
                  ✨ Bienvenido de nuevo
                </AppText>
              </View>
              <AppText variant="h1" style={styles.title}>
                Iniciar sesión
              </AppText>
              <AppText variant="body" style={styles.subtitle}>
                Accede con tu cuenta de padre para supervisar el progreso de tus hijos.
              </AppText>
            </View>

            <View style={styles.card}>
              <LinearGradient
                colors={['rgba(255,255,255,0.97)', 'rgba(255,253,248,0.94)']}
                style={styles.cardGradient}
              >
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
                    style={styles.input}
                  />
                  <PasswordInputField
                    label="Contraseña"
                    value={contrasena}
                    onChangeText={setContrasena}
                    error={fieldErrors.contrasena}
                    style={styles.input}
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
                  style={styles.childButton}
                />
                <Button
                  label="¿Olvidaste tu contraseña?"
                  variant="ghost"
                  onPress={() => navigation.navigate('ForgotPassword')}
                  style={styles.ghostButton}
                />
                <Button
                  label="Crear cuenta"
                  variant="secondary"
                  onPress={() => navigation.navigate('Register')}
                  fullWidth
                  style={styles.registerButton}
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
  flex: {
    flex: 1,
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
    backgroundColor: 'rgba(255,255,255,0.45)',
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
    backgroundColor: 'rgba(255,255,255,0.28)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.45)',
  },
  badgeText: {
    color: '#FFFFFF',
    fontFamily: theme.fonts.semiBold,
    letterSpacing: 0.4,
  },
  title: {
    color: '#FFFFFF',
    textAlign: 'center',
    textShadowColor: 'rgba(0,0,0,0.18)',
    textShadowOffset: { width: 0, height: 2 },
    textShadowRadius: 8,
  },
  subtitle: {
    color: 'rgba(255,255,255,0.92)',
    textAlign: 'center',
    maxWidth: 320,
    lineHeight: 22,
  },
  card: {
    borderRadius: theme.radii.xl,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.65)',
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
  input: {
    backgroundColor: 'rgba(255,255,255,0.88)',
    borderColor: 'rgba(46, 125, 50, 0.22)',
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
