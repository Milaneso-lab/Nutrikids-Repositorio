import React, { useState } from 'react';
import { Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import type { AvatarConfig } from '@features/familia/types/familia.types';
import { resolveAvatar } from '@features/familia/utils/avatarConfig';
import { KeyboardAwareScroll } from '@shared/components/layout/KeyboardAwareScroll';
import type { ChildStackParamList } from '@navigation/types';

import { KidActionButton } from '../components/KidActionButton';
import { KidAvatarDisplay } from '../components/KidAvatarDisplay';
import { KidAvatarPicker } from '../components/KidAvatarPicker';
import { KidCard } from '../components/KidCard';
import { KidScreenBackground } from '../components/KidScreenBackground';
import { useAvatarEditor } from '../hooks/useAvatarEditor';
import { useThemedKidStyles } from '../hooks/useThemedKidStyles';
import { useKidTheme } from '../providers/KidThemeProvider';

type Props = NativeStackScreenProps<ChildStackParamList, 'AvatarEditor'>;

export function AvatarEditorScreen({ navigation }: Props): React.JSX.Element {
  const { gradients } = useKidTheme();
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

  const { activeChild, saving, error, saveAvatar } = useAvatarEditor();

  const initial = resolveAvatar(activeChild?.avatar_config ?? null);
  const [avatar, setAvatar] = useState<AvatarConfig>(initial);
  const [companion, setCompanion] = useState(activeChild?.companion ?? '🦊');

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
    const success = await saveAvatar({ ...avatar, companion });
    if (success) {
      navigation.goBack();
    }
  }

  return (
    <KidScreenBackground gradient={gradients.adventure}>
      <KeyboardAwareScroll contentContainerStyle={styles.content}>
        <Text style={styles.title}>Personaliza tu avatar</Text>
        <Text style={styles.subtitle}>Elige cómo quieres que te vean en tu aventura</Text>

        <View style={styles.preview}>
          <KidAvatarDisplay avatarConfig={{ ...avatar, companion }} size="hero" />
        </View>

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
          label="Guardar mi avatar"
          emoji="💾"
          loading={saving}
          onPress={() => void handleSave()}
        />
      </KeyboardAwareScroll>
    </KidScreenBackground>
  );
}
