import React, { useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { theme } from '@core/theme';
import { AuthLayout } from '@features/auth/components/AuthLayout';
import { AuthText } from '@features/auth/components/AuthText';
import { FormErrorBanner } from '@features/auth/components/FormErrorBanner';
import { TextInputField } from '@features/auth/components/TextInputField';
import { useAuth } from '@features/auth/hooks/useAuth';
import { useAuthTheme } from '@features/auth/providers/AuthThemeProvider';
import { validateForgotPasswordForm } from '@features/auth/validation/authValidation';
import { AuthStackParamList } from '@navigation/types';
import { Button } from '@shared/components/ui/Button';
import { useNetworkStatus } from '@shared/hooks/useNetworkStatus';

type Props = NativeStackScreenProps<AuthStackParamList, 'ForgotPassword'>;

export function ForgotPasswordScreen({ navigation }: Props): React.JSX.Element {
  const { colors } = useAuthTheme();
  const { forgotPassword, loading, error, clearError } = useAuth();
  const { isConnected } = useNetworkStatus();
  const [email, setEmail] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [successMessage, setSuccessMessage] = useState<string | null>(null);

  const handleSubmit = async (): Promise<void> => {
    clearError();
    setSuccessMessage(null);
    const validation = validateForgotPasswordForm(email);
    setFieldErrors(validation.errors);
    if (!validation.valid || !isConnected) {
      return;
    }

    const message = await forgotPassword(email);
    if (message) {
      const normalizedEmail = email.trim().toLowerCase();
      setSuccessMessage(
        `${message} Revisa tu bandeja de entrada (y spam). El código tiene 6 números y expira en 30 minutos.`,
      );
      setTimeout(() => {
        navigation.navigate('ResetPassword', { email: normalizedEmail });
      }, 1200);
    }
  };

  return (
    <AuthLayout>
      <AuthText variant="h2">Recuperar contraseña</AuthText>
      <AuthText variant="body" tone="secondary">
        Te enviaremos un código de 6 números a tu correo si está registrado en NutriKids.
      </AuthText>

      {!isConnected ? <FormErrorBanner message="Sin conexión a internet." /> : null}
      {error ? <FormErrorBanner message={error} /> : null}
      {successMessage ? (
        <View
          style={[
            styles.success,
            { backgroundColor: colors.accentSoft, borderColor: colors.accentMuted },
          ]}
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
      </View>

      <Button label="Enviar código" onPress={() => void handleSubmit()} loading={loading} fullWidth />
      <Button
        label="Ya tengo el código"
        variant="secondary"
        onPress={() => navigation.navigate('ResetPassword', { email: email.trim().toLowerCase() })}
        fullWidth
      />
      <Button label="Volver" variant="ghost" onPress={() => navigation.goBack()} />
    </AuthLayout>
  );
}

const styles = StyleSheet.create({
  form: {
    gap: theme.spacing.md,
    marginTop: theme.spacing.sm,
  },
  success: {
    borderRadius: theme.radii.md,
    padding: theme.spacing.sm,
    borderWidth: 1,
  },
});
