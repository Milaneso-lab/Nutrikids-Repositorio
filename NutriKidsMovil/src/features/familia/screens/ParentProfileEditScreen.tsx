import React, { useCallback, useState } from 'react';
import { Image, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useFocusEffect } from '@react-navigation/native';

import { theme } from '@core/theme';
import { TextInputField } from '@features/auth/components/TextInputField';
import { ParentScreen } from '../components/ParentScreen';
import { ParentCard } from '../components/ParentCard';
import { ParentText } from '../components/ParentText';
import { KeyboardAwareScroll } from '@shared/components/layout/KeyboardAwareScroll';
import { Button } from '@shared/components/ui/Button';
import { ErrorMessage } from '@shared/components/ui/ErrorMessage';
import { useAppStore } from '@state/stores/appStore';
import type { FamilyStackParamList } from '@navigation/types';
import { getFriendlyErrorMessage } from '@core/errors/friendlyMessages';

import { AvatarPicker } from '../components/AvatarPicker';
import { parentProfileService } from '../services/parentProfileService';
import type { AvatarConfig } from '../types/familia.types';
import { DEFAULT_AVATAR, resolveAvatar } from '../utils/avatarConfig';

type Props = NativeStackScreenProps<FamilyStackParamList, 'ParentProfileEdit'>;

export function ParentProfileEditScreen(_props: Props): React.JSX.Element {
  const user = useAppStore((state) => state.user);
  const setUser = useAppStore((state) => state.setUser);
  const avatarConfig = useAppStore((state) => state.avatarConfig);
  const setAvatarConfig = useAppStore((state) => state.setAvatarConfig);

  const [nombre, setNombre] = useState(user?.nombre ?? '');
  const [apellidoPaterno, setApellidoPaterno] = useState(user?.apellidoPaterno ?? '');
  const [avatar, setAvatar] = useState<AvatarConfig>(resolveAvatar(avatarConfig ?? null));
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  useFocusEffect(
    useCallback(() => {
      if (!user) {
        return;
      }
      setNombre(user.nombre);
      setApellidoPaterno(user.apellidoPaterno ?? '');
      void parentProfileService.getAvatar(user.idUsuario).then((stored) => {
        setAvatar(resolveAvatar(stored ?? avatarConfig ?? null));
      });
    }, [user, avatarConfig]),
  );

  if (!user) {
    return (
      <ParentScreen>
        <View style={styles.center}>
          <ParentText variant="body" tone="secondary">
            No hay sesión activa
          </ParentText>
        </View>
      </ParentScreen>
    );
  }

  async function handleSave(): Promise<void> {
    const trimmedNombre = nombre.trim();
    const trimmedApellido = apellidoPaterno.trim();

    if (!trimmedNombre) {
      setError('El nombre es obligatorio');
      return;
    }
    if (!trimmedApellido) {
      setError('El apellido es obligatorio');
      return;
    }

    setLoading(true);
    setError(null);
    setSuccess(false);

    try {
      const updated = await parentProfileService.updateProfile({
        nombre: trimmedNombre,
        apellido_paterno: trimmedApellido,
      });
      await parentProfileService.saveAvatar(user.idUsuario, avatar);
      setUser({
        idUsuario: updated.id_usuario,
        email: updated.email,
        nombre: updated.nombre,
        apellidoPaterno: updated.apellido_paterno,
        rol: updated.rol,
      });
      setAvatarConfig(avatar);
      setSuccess(true);
    } catch (err) {
      setError(getFriendlyErrorMessage(err));
    } finally {
      setLoading(false);
    }
  }

  const preview = resolveAvatar(avatar);

  return (
    <ParentScreen>
      <KeyboardAwareScroll contentContainerStyle={styles.content} keyboardVerticalOffset={88}>
        <ParentCard style={styles.hero}>
          <View style={[styles.avatarPreview, { backgroundColor: preview.backgroundColor }]}>
            {preview.photoUri ? (
              <Image source={{ uri: preview.photoUri }} style={styles.photo} accessibilityIgnoresInvertColors />
            ) : (
              <ParentText style={styles.emoji}>{preview.emoji ?? DEFAULT_AVATAR.emoji}</ParentText>
            )}
          </View>
          <ParentText variant="h2" style={styles.centerText}>
            Editar mi perfil
          </ParentText>
          <ParentText variant="bodySmall" tone="secondary" style={styles.centerText}>
            {user.email}
          </ParentText>
        </ParentCard>

        <ParentCard style={styles.form}>
          <TextInputField
            label="Nombre"
            value={nombre}
            onChangeText={setNombre}
            autoCapitalize="words"
            placeholder="Tu nombre"
          />
          <TextInputField
            label="Apellido paterno"
            value={apellidoPaterno}
            onChangeText={setApellidoPaterno}
            autoCapitalize="words"
            placeholder="Tu apellido"
          />

          <ParentText variant="label" tone="secondary">
            Foto o avatar
          </ParentText>
          <AvatarPicker value={avatar} onChange={setAvatar} />
        </ParentCard>

        {error ? <ErrorMessage message={error} /> : null}
        {success ? (
          <ParentText variant="bodySmall" style={styles.success}>
            Perfil actualizado correctamente
          </ParentText>
        ) : null}

        <Button label="Guardar cambios" loading={loading} onPress={() => void handleSave()} fullWidth />
      </KeyboardAwareScroll>
    </ParentScreen>
  );
}

const styles = StyleSheet.create({
  content: {
    padding: theme.spacing.lg,
    gap: theme.spacing.lg,
    paddingBottom: theme.spacing['3xl'],
  },
  center: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  hero: {
    alignItems: 'center',
    gap: theme.spacing.xs,
  },
  centerText: {
    textAlign: 'center',
  },
  avatarPreview: {
    width: 88,
    height: 88,
    borderRadius: 44,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
    marginBottom: theme.spacing.sm,
  },
  photo: {
    width: '100%',
    height: '100%',
  },
  emoji: {
    fontSize: 40,
  },
  form: {
    gap: theme.spacing.md,
  },
  success: {
    color: theme.colors.success,
    textAlign: 'center',
  },
});
