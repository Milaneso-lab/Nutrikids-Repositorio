import React, { useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { theme } from '@core/theme';
import { AuthLayout } from '@features/auth/components/AuthLayout';
import { AuthText } from '@features/auth/components/AuthText';
import { FormErrorBanner } from '@features/auth/components/FormErrorBanner';
import { PasswordInputField, TextInputField } from '@features/auth/components/TextInputField';
import { useAuth } from '@features/auth/hooks/useAuth';
import { validateRegisterForm } from '@features/auth/validation/authValidation';
import { AuthStackParamList } from '@navigation/types';
import { Button } from '@shared/components/ui/Button';
import { useNetworkStatus } from '@shared/hooks/useNetworkStatus';

type Props = NativeStackScreenProps<AuthStackParamList, 'Register'>;

export function RegisterScreen({ navigation }: Props): React.JSX.Element {
  const { register, loading, error, clearError } = useAuth();
  const { isConnected } = useNetworkStatus();
  const [nombre, setNombre] = useState('');
  const [apellidoPaterno, setApellidoPaterno] = useState('');
  const [apellidoMaterno, setApellidoMaterno] = useState('');
  const [email, setEmail] = useState('');
  const [contrasena, setContrasena] = useState('');
  const [confirmarContrasena, setConfirmarContrasena] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  const handleSubmit = async (): Promise<void> => {
    clearError();
    const validation = validateRegisterForm({
      nombre,
      apellido_paterno: apellidoPaterno,
      apellido_materno: apellidoMaterno,
      email,
      contrasena,
      confirmarContrasena,
    });
    setFieldErrors(validation.errors);
    if (!validation.valid || !isConnected) {
      return;
    }

    await register({
      nombre: nombre.trim(),
      apellido_paterno: apellidoPaterno.trim(),
      apellido_materno: apellidoMaterno.trim() || undefined,
      email,
      contrasena,
    });
  };

  return (
    <AuthLayout>
      <AuthText variant="h2">Crear cuenta</AuthText>
      <AuthText variant="body" tone="secondary">
        Registro exclusivo para padres. Podrás vincular perfiles de niños más adelante.
      </AuthText>

      {!isConnected ? <FormErrorBanner message="Sin conexión a internet." /> : null}
      {error ? <FormErrorBanner message={error} /> : null}

      <View style={styles.form}>
        <TextInputField label="Nombre" value={nombre} onChangeText={setNombre} error={fieldErrors.nombre} />
        <TextInputField
          label="Apellido paterno"
          value={apellidoPaterno}
          onChangeText={setApellidoPaterno}
          error={fieldErrors.apellido_paterno}
        />
        <TextInputField
          label="Apellido materno (opcional)"
          value={apellidoMaterno}
          onChangeText={setApellidoMaterno}
        />
        <TextInputField
          label="Correo electrónico"
          value={email}
          onChangeText={setEmail}
          keyboardType="email-address"
          autoCapitalize="none"
          error={fieldErrors.email}
        />
        <PasswordInputField
          label="Contraseña"
          value={contrasena}
          onChangeText={setContrasena}
          error={fieldErrors.contrasena}
        />
        <PasswordInputField
          label="Confirmar contraseña"
          value={confirmarContrasena}
          onChangeText={setConfirmarContrasena}
          error={fieldErrors.confirmarContrasena}
        />
      </View>

      <Button label="Registrarme" onPress={() => void handleSubmit()} loading={loading} fullWidth />
      <Button label="Ya tengo cuenta" variant="ghost" onPress={() => navigation.navigate('Login')} />
    </AuthLayout>
  );
}

const styles = StyleSheet.create({
  form: {
    gap: theme.spacing.md,
    marginTop: theme.spacing.sm,
  },
});
