import React, { useRef, useState } from 'react';
import { Text, TextInput, View, type NativeSyntheticEvent, type TextInputFocusEventData } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { getFriendlyErrorMessage } from '@core/errors/friendlyMessages';
import type { AvatarConfig } from '@features/familia/types/familia.types';
import { resolveAvatar } from '@features/familia/utils/avatarConfig';
import { KeyboardAwareScroll, useKeyboardScroll } from '@shared/components/layout/KeyboardAwareScroll';
import type { ChildStackParamList } from '@navigation/types';

import { KidActionButton } from '../components/KidActionButton';
import { KidAvatarDisplay } from '../components/KidAvatarDisplay';
import { KidAvatarPicker } from '../components/KidAvatarPicker';
import { KidCard } from '../components/KidCard';
import { KidScreenBackground } from '../components/KidScreenBackground';
import { childProfileService } from '../services/childProfileService';
import { useChildSessionStore } from '../store/childSessionStore';
import { useThemedKidStyles } from '../hooks/useThemedKidStyles';
import { useKidTheme } from '../providers/KidThemeProvider';

type Props = NativeStackScreenProps<ChildStackParamList, 'ChildProfileEdit'>;

export function ChildProfileEditScreen({ navigation }: Props): React.JSX.Element {
  const { colors, gradients } = useKidTheme();
  const styles = useThemedKidStyles((t) => ({
    content: {
      padding: t.spacing.lg,
      paddingBottom: t.spacing['3xl'],
      gap: t.spacing.md,
    },
    center: {
      flex: 1,
      alignItems: 'center',
      justifyContent: 'center',
    },
    title: {
      fontFamily: t.fonts.extraBold,
      fontSize: 26,
      color: t.colors.textOnGradient,
      textAlign: 'center',
    },
    subtitle: {
      fontFamily: t.fonts.medium,
      fontSize: 15,
      color: t.colors.textOnGradientMuted,
      textAlign: 'center',
    },
    preview: {
      alignItems: 'center',
      paddingVertical: t.spacing.md,
    },
    fieldCard: {
      gap: t.spacing.xs,
    },
    label: {
      fontFamily: t.fonts.bold,
      fontSize: 14,
      color: t.colors.ink,
    },
    input: {
      fontFamily: t.fonts.medium,
      fontSize: 16,
      color: t.colors.inputText,
      borderWidth: 1,
      borderColor: t.colors.inputBorder,
      backgroundColor: t.colors.inputBackground,
      borderRadius: t.radii.button,
      paddingHorizontal: t.spacing.md,
      paddingVertical: t.spacing.sm,
      minHeight: 48,
    },
    error: {
      fontFamily: t.fonts.bold,
      color: t.colors.textOnGradient,
    },
    errorBanner: {
      fontFamily: t.fonts.medium,
      fontSize: 14,
      color: '#B91C1C',
      backgroundColor: '#FEE2E2',
      padding: t.spacing.sm,
      borderRadius: t.radii.button,
      textAlign: 'center',
    },
  }));

  const activeChild = useChildSessionStore((s) => s.activeChild);
  const updateActiveChild = useChildSessionStore((s) => s.updateActiveChild);

  const initialAvatar = resolveAvatar(activeChild?.avatar_config ?? null);
  const [nombre, setNombre] = useState(activeChild?.nombre ?? '');
  const [avatar, setAvatar] = useState<AvatarConfig>(initialAvatar);
  const [companion, setCompanion] = useState(activeChild?.companion ?? '🦊');
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const nombreInputRef = useRef<TextInput>(null);
  const keyboardScroll = useKeyboardScroll();

  function handleNombreFocus(_event: NativeSyntheticEvent<TextInputFocusEventData>): void {
    if (nombreInputRef.current) {
      keyboardScroll?.scrollToFocused(nombreInputRef.current);
    }
  }

  if (!activeChild) {
    return (
      <KidScreenBackground gradient={gradients.adventure}>
        <View style={styles.center}>
          <Text style={styles.error}>No hay sesión activa</Text>
        </View>
      </KidScreenBackground>
    );
  }

  async function handleSave(): Promise<void> {
    const trimmedNombre = nombre.trim();
    if (!trimmedNombre) {
      setError('Escribe tu nombre');
      return;
    }

    setSaving(true);
    setError(null);

    try {
      const mergedAvatar: AvatarConfig = { ...avatar, companion };
      const updated = await childProfileService.updateProfile(
        activeChild.ninoId,
        {
          nombre: trimmedNombre,
          avatar_config: mergedAvatar,
        },
        activeChild,
      );
      updateActiveChild(updated);
      navigation.goBack();
    } catch (err) {
      setError(getFriendlyErrorMessage(err));
    } finally {
      setSaving(false);
    }
  }

  return (
    <KidScreenBackground gradient={gradients.adventure}>
      <KeyboardAwareScroll contentContainerStyle={styles.content} keyboardVerticalOffset={88}>
        <Text style={styles.title}>Editar mi perfil</Text>
        <Text style={styles.subtitle}>Actualiza tu nombre y cómo te ven en la app</Text>

        <View style={styles.preview}>
          <KidAvatarDisplay avatarConfig={{ ...avatar, companion }} size="hero" />
        </View>

        <KidCard style={styles.fieldCard}>
          <Text style={styles.label}>Mi nombre</Text>
          <TextInput
            ref={nombreInputRef}
            accessibilityLabel="Mi nombre"
            value={nombre}
            onChangeText={setNombre}
            onFocus={handleNombreFocus}
            placeholder="Escribe tu nombre"
            placeholderTextColor={colors.inputPlaceholder}
            style={styles.input}
            autoCapitalize="words"
          />
        </KidCard>

        <KidCard>
          <KidAvatarPicker
            value={avatar}
            companion={companion}
            onChange={setAvatar}
            onCompanionChange={setCompanion}
          />
        </KidCard>

        {error ? <Text style={styles.errorBanner}>{error}</Text> : null}

        <KidActionButton
          label="Guardar cambios"
          emoji="💾"
          loading={saving}
          onPress={() => void handleSave()}
        />
      </KeyboardAwareScroll>
    </KidScreenBackground>
  );
}
