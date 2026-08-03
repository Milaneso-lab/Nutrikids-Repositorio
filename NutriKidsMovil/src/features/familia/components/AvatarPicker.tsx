import React, { useState } from 'react';
import { Alert, Image, Platform, Pressable, ScrollView, StyleSheet, View } from 'react-native';
import * as ImagePicker from 'expo-image-picker';

import { theme } from '@core/theme';
import { AppText } from '@shared/components/ui/Text';

import { ParentText } from './ParentText';
import { useParentTheme } from '../providers/ParentThemeProvider';
import type { AvatarConfig } from '../types/familia.types';
import { AVATAR_PRESETS, resolveAvatar } from '../utils/avatarConfig';

interface AvatarPickerProps {
  value: AvatarConfig;
  onChange: (avatar: AvatarConfig) => void;
}

export function AvatarPicker({ value, onChange }: AvatarPickerProps): React.JSX.Element {
  const { colors } = useParentTheme();
  const [picking, setPicking] = useState(false);
  const avatar = resolveAvatar(value);

  async function pickPhoto(): Promise<void> {
    setPicking(true);
    try {
      const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (!permission.granted) {
        Alert.alert('Permiso necesario', 'Necesitamos acceso a tus fotos para elegir un avatar.');
        return;
      }

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ['images'],
        allowsEditing: true,
        aspect: [1, 1],
        quality: 0.8,
      });

      if (!result.canceled && result.assets[0]?.uri) {
        onChange({ ...avatar, photoUri: result.assets[0].uri });
      }
    } finally {
      setPicking(false);
    }
  }

  function selectPreset(preset: AvatarConfig): void {
    onChange({ ...preset, photoUri: undefined });
  }

  return (
    <View style={styles.wrapper}>
      <View style={[styles.preview, { backgroundColor: avatar.backgroundColor, borderColor: colors.accentMuted }]}>
        {avatar.photoUri ? (
          <Image source={{ uri: avatar.photoUri }} style={styles.previewImage} accessibilityIgnoresInvertColors />
        ) : (
          <AppText style={styles.previewEmoji}>{avatar.emoji}</AppText>
        )}
      </View>

      <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.presets}>
        {AVATAR_PRESETS.map((preset) => {
          const selected = preset.emoji === avatar.emoji && !avatar.photoUri;
          return (
            <Pressable
              key={`${preset.emoji}-${preset.backgroundColor}`}
              accessibilityRole="button"
              accessibilityState={{ selected }}
              accessibilityLabel={`Avatar ${preset.emoji}`}
              onPress={() => selectPreset(preset)}
              style={[
                styles.preset,
                { backgroundColor: preset.backgroundColor },
                selected && { borderColor: colors.accent },
              ]}
            >
              <AppText style={styles.presetEmoji}>{preset.emoji}</AppText>
            </Pressable>
          );
        })}
      </ScrollView>

      <Pressable
        accessibilityRole="button"
        accessibilityLabel="Elegir foto desde galería"
        onPress={() => void pickPhoto()}
        disabled={picking}
        style={styles.photoButton}
      >
        <ParentText variant="caption" tone="accent">
          {picking ? 'Abriendo galería…' : 'Elegir foto (opcional)'}
        </ParentText>
      </Pressable>

      {Platform.OS === 'web' ? (
        <ParentText variant="caption" tone="secondary">
          En web, la foto se guardará en este dispositivo.
        </ParentText>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    gap: theme.spacing.sm,
    alignItems: 'center',
  },
  preview: {
    width: 96,
    height: 96,
    borderRadius: theme.radii.pill,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
    borderWidth: 2,
  },
  previewImage: {
    width: '100%',
    height: '100%',
  },
  previewEmoji: {
    fontSize: 40,
  },
  presets: {
    gap: theme.spacing.sm,
    paddingVertical: theme.spacing.xs,
  },
  preset: {
    width: 48,
    height: 48,
    borderRadius: theme.radii.pill,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    borderColor: 'transparent',
  },
  presetEmoji: {
    fontSize: 24,
  },
  photoButton: {
    minHeight: 44,
    justifyContent: 'center',
    paddingHorizontal: theme.spacing.md,
  },
});
