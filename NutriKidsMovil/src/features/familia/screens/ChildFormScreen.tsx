import React from 'react';
import {
  ActivityIndicator,
  StyleSheet,
  View,
} from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { theme } from '@core/theme';
import { TextInputField } from '@features/auth/components/TextInputField';
import { ParentScreen } from '../components/ParentScreen';
import { ParentText } from '../components/ParentText';
import { useParentTheme } from '../providers/ParentThemeProvider';
import { KeyboardAwareScroll } from '@shared/components/layout/KeyboardAwareScroll';
import { Button } from '@shared/components/ui/Button';
import { ErrorMessage } from '@shared/components/ui/ErrorMessage';
import type { FamilyStackParamList } from '@navigation/types';

import { AvatarPicker } from '../components/AvatarPicker';
import { DateInputField } from '../components/DateInputField';
import { SexoSelector } from '../components/SexoSelector';
import { useNinoForm } from '../hooks/useNinoForm';
import { calculateAge } from '../utils/age';

type Props = NativeStackScreenProps<FamilyStackParamList, 'ChildForm'>;

export function ChildFormScreen({ navigation, route }: Props): React.JSX.Element {
  const { colors } = useParentTheme();
  const ninoId = route.params?.ninoId;

  const { values, errors, setField, submit, submitting, loadingInitial, formError, isEditing } = useNinoForm({
    ninoId,
    onSuccess: () => {
      navigation.goBack();
    },
  });

  const computedAge = values.fecha_nacimiento ? calculateAge(values.fecha_nacimiento) : null;

  if (loadingInitial) {
    return (
      <ParentScreen>
        <View style={styles.loading}>
          <ActivityIndicator size="large" color={colors.accent} />
          <ParentText variant="bodySmall" tone="secondary">
            Cargando datos…
          </ParentText>
        </View>
      </ParentScreen>
    );
  }

  return (
    <ParentScreen>
      <KeyboardAwareScroll contentContainerStyle={styles.content} keyboardVerticalOffset={88}>
          <ParentText variant="h2">{isEditing ? 'Editar perfil' : 'Registrar hijo'}</ParentText>
          <ParentText variant="bodySmall" tone="secondary">
            Completa la información básica. Puedes actualizarla cuando lo necesites.
          </ParentText>

          {formError ? <ErrorMessage message={formError} /> : null}

          <AvatarPicker
            value={values.avatar}
            onChange={(avatar) => setField('avatar', avatar)}
          />

          <TextInputField
            label="Nombre"
            value={values.nombre}
            onChangeText={(text) => setField('nombre', text)}
            error={errors.nombre}
            autoCapitalize="words"
          />

          <TextInputField
            label="Apellidos"
            value={values.apellidos}
            onChangeText={(text) => setField('apellidos', text)}
            error={errors.apellidos}
            autoCapitalize="words"
          />

          <DateInputField
            label="Fecha de nacimiento"
            value={values.fecha_nacimiento}
            onChange={(date) => setField('fecha_nacimiento', date)}
            error={errors.fecha_nacimiento}
          />

          {computedAge !== null ? (
            <ParentText variant="caption" tone="secondary">
              Edad calculada: {computedAge === 1 ? '1 año' : `${computedAge} años`}
            </ParentText>
          ) : null}

          <SexoSelector
            value={values.sexo}
            onChange={(sexo) => setField('sexo', sexo)}
            error={errors.sexo}
          />

          <TextInputField
            label="Peso inicial (kg)"
            value={values.peso_actual_kg}
            onChangeText={(text) => setField('peso_actual_kg', text)}
            error={errors.peso_actual_kg}
            keyboardType="decimal-pad"
            placeholder="Ej. 25.5"
          />

          <TextInputField
            label="Estatura (cm)"
            value={values.talla_actual_cm}
            onChangeText={(text) => setField('talla_actual_cm', text)}
            error={errors.talla_actual_cm}
            keyboardType="decimal-pad"
            placeholder="Ej. 120"
          />

          <TextInputField
            label="Objetivo nutricional (opcional)"
            value={values.objetivoNutricional}
            onChangeText={(text) => setField('objetivoNutricional', text)}
            error={errors.objetivoNutricional}
            multiline
            numberOfLines={3}
            placeholder="Ej. Aumentar consumo de frutas y verduras"
          />

          <TextInputField
            label="Nivel inicial de gamificación"
            value={values.nivelInicial}
            onChangeText={(text) => setField('nivelInicial', text)}
            error={errors.nivelInicial}
            keyboardType="number-pad"
            placeholder="1"
          />

          <Button
            label={isEditing ? 'Guardar cambios' : 'Registrar hijo'}
            loading={submitting}
            fullWidth
            onPress={() => void submit()}
          />
      </KeyboardAwareScroll>
    </ParentScreen>
  );
}

const styles = StyleSheet.create({
  content: {
    padding: theme.spacing.lg,
    gap: theme.spacing.md,
    paddingBottom: theme.spacing['3xl'],
  },
  loading: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: theme.spacing.sm,
  },
});
