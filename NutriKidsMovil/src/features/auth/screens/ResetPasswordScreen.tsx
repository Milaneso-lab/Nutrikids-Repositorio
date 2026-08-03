import React, { useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { theme } from '@core/theme';
import { AuthLayout } from '@features/auth/components/AuthLayout';
import { AuthText } from '@features/auth/components/AuthText';
import { FormErrorBanner } from '@features/auth/components/FormErrorBanner';
import { PasswordInputField, PinInputField, TextInputField } from '@features/auth/components/TextInputField';
import { useAuth } from '@features/auth/hooks/useAuth';
import { useAuthTheme } from '@features/auth/providers/AuthThemeProvider';
import { validateResetPasswordForm } from '@features/auth/validation/authValidation';
import { AuthStackParamList } from '@navigation/types';
import { Button } from '@shared/components/ui/Button';
import { useNetworkStatus } from '@shared/hooks/useNetworkStatus';

type Props = NativeStackScreenProps<AuthStackParamList, 'ResetPassword'>;

export function ResetPasswordScreen({ navigation, route }: Props): React.JSX.Element {
  const { colors } = useAuthTheme();
  const { resetPassword, forgotPassword, loading, error, clearError } = useAuth();
  const { isConnected } = useNetworkStatus();
  const [email, setEmail] = useState(route.params?.email ?? '');
  const [token, setToken] = useState(route.params?.token ?? '');
  const [nuevaContrasena, setNuevaContrasena] = useState('');
  const [confirmarContrasena, setConfirmarContrasena] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [resendMessage, setResendMessage] = useState<string | null>(null);

  const handleSubmit = async (): Promise<void> => {
    clearError();
    setSuccessMessage(null);
    setResendMessage(null);
    const validation = validateResetPasswordForm({
      email,
      token,
      nueva_contrasena: nuevaContrasena,
      confirmarContrasena,
    });
    setFieldErrors(validation.errors);
    if (!validation.valid || !isConnected) {
      return;
    }

    const message = await resetPassword({
      email,
      token: token.trim(),
      nueva_contrasena: nuevaContrasena,
    });

    if (message) {
      setSuccessMessage(message);
      setTimeout(() => navigation.replace('Login'), 1500);
    }
  };

  const handleResendCode = async (): Promise<void> => {
    clearError();
    setResendMessage(null);
    if (!email.trim() || !isConnected) {
      return;
    }
    const message = await forgotPassword(email);
    if (message) {
      setResendMessage('Te enviamos un código nuevo. Revisa tu correo.');
    }
  };

  return (
    <AuthLayout>
      <AuthText variant="h2">Restablecer contraseña</AuthText>
      <AuthText variant="body" tone="secondary">
        Ingresa el código de 6 números que recibiste por correo y elige una contraseña nueva.
      </AuthText>

      {!isConnected ? <FormErrorBanner message="Sin conexión a internet." /> : null}
      {error ? <FormErrorBanner message={error} /> : null}
      {resendMessage ? (
        <View
          style={[styles.info, { backgroundColor: colors.surface, borderColor: colors.border }]}
          accessibilityRole="alert"
        >
          <AuthText variant="bodySmall" tone="secondary">
            {resendMessage}
          </AuthText>
        </View>
      ) : null}
      {successMessage ? (
        <View
          style={[styles.success, { backgroundColor: colors.accentSoft, borderColor: colors.accentMuted }]}
          accessibilityRole="alert"
        >
          <AuthText variant="bodySmall" tone="primary">
            {successMessage}
          </AuthText>
        </View>
      ) : null}

      <View style={styles.form}>
        <TextInputField
          label="Correo electrónico"
          value={email}
          onChangeText={setEmail}
          keyboardType="email-address"
          autoCapitalize="none"
          error={fieldErrors.email}
        />
        <PinInputField
          label="Código de verificación"
          value={token}
          onChangeText={setToken}
          error={fieldErrors.token}
          placeholder="6 números"
          maxLength={6}
        />
        <PasswordInputField
          label="Nueva contraseña"
          value={nuevaContrasena}
          onChangeText={setNuevaContrasena}
          error={fieldErrors.nueva_contrasena}
        />
        <PasswordInputField
          label="Confirmar contraseña"
          value={confirmarContrasena}
          onChangeText={setConfirmarContrasena}
          error={fieldErrors.confirmarContrasena}
        />
      </View>

      <Button label="Actualizar contraseña" onPress={() => void handleSubmit()} loading={loading} fullWidth />
      <Button
        label="Reenviar código"
        variant="secondary"
        onPress={() => void handleResendCode()}
        loading={loading}
        fullWidth
      />
      <Button label="Volver al inicio de sesión" variant="ghost" onPress={() => navigation.navigate('Login')} />
    </AuthLayout>
  );
}

const styles = StyleSheet.create({
  form: {
    gap: theme.spacing.md,
    marginTop: theme.spacing.sm,
  },
  info: {
    borderRadius: theme.radii.md,
    padding: theme.spacing.sm,
    borderWidth: 1,
  },
  success: {
    borderRadius: theme.radii.md,
    padding: theme.spacing.sm,
    borderWidth: 1,
  },
});
