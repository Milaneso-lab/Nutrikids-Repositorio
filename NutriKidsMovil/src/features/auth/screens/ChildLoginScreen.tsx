import React, { useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { theme } from '@core/theme';
import { getFriendlyErrorMessage } from '@core/errors/friendlyMessages';
import { AuthLayout } from '@features/auth/components/AuthLayout';
import { AuthText } from '@features/auth/components/AuthText';
import { FormErrorBanner } from '@features/auth/components/FormErrorBanner';
import { PinInputField, TextInputField } from '@features/auth/components/TextInputField';
import { AuthStackParamList } from '@navigation/types';
import { childAuthService } from '@services/auth/childAuthService';
import { Button } from '@shared/components/ui/Button';
import { useNetworkStatus } from '@shared/hooks/useNetworkStatus';

type Props = NativeStackScreenProps<AuthStackParamList, 'ChildLogin'>;

export function ChildLoginScreen({ navigation }: Props): React.JSX.Element {
  const { isConnected } = useNetworkStatus();
  const [codigo, setCodigo] = useState('');
  const [pin, setPin] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  function validate(): boolean {
    const errors: Record<string, string> = {};
    if (!codigo.trim()) {
      errors.codigo = 'Ingresa el código que te dio mamá o papá';
    }
    if (!/^\d{4,6}$/.test(pin)) {
      errors.pin = 'El PIN debe tener entre 4 y 6 números';
    }
    setFieldErrors(errors);
    return Object.keys(errors).length === 0;
  }

  async function handleSubmit(): Promise<void> {
    setError(null);
    if (!validate() || !isConnected) {
      return;
    }

    setLoading(true);
    try {
      await childAuthService.acceso({
        codigo_vinculacion: codigo,
        pin,
      });
    } catch (err) {
      setError(getFriendlyErrorMessage(err));
    } finally {
      setLoading(false);
    }
  }

  return (
    <AuthLayout>
      <AuthText variant="h2">¡Hola, pequeño aventurero!</AuthText>
      <AuthText variant="body" tone="secondary">
        Pide a tu papá o mamá el código de vinculación y el PIN secreto para entrar a tu aventura.
      </AuthText>

      {!isConnected ? <FormErrorBanner message="Sin conexión a internet." /> : null}
      {error ? <FormErrorBanner message={error} /> : null}

      <View style={styles.form}>
        <TextInputField
          label="Código de vinculación"
          value={codigo}
          onChangeText={(text) => setCodigo(text.toUpperCase())}
          autoCapitalize="characters"
          autoCorrect={false}
          placeholder="Ej. ABC12345"
          error={fieldErrors.codigo}
        />
        <PinInputField
          label="Tu PIN"
          value={pin}
          onChangeText={setPin}
          error={fieldErrors.pin}
          placeholder="4 a 6 números"
        />
      </View>

      <Button
        label="Entrar a mi aventura"
        onPress={() => void handleSubmit()}
        loading={loading}
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
});
