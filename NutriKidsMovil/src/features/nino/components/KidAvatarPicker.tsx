import React from 'react';
import { Pressable, ScrollView, Text, View } from 'react-native';

import type { AvatarConfig } from '@features/familia/types/familia.types';
import { AVATAR_PRESETS, DEFAULT_AVATAR } from '@features/familia/utils/avatarConfig';

import { useThemedKidStyles } from '../hooks/useThemedKidStyles';

const COMPANION_PRESETS = ['🦊', '🐰', '🐼', '🦁', '🐸', '🦄'] as const;

interface KidAvatarPickerProps {
  value: AvatarConfig;
  companion?: string;
  onChange: (avatar: AvatarConfig) => void;
  onCompanionChange?: (companion: string) => void;
}

export function KidAvatarPicker({
  value,
  companion,
  onChange,
  onCompanionChange,
}: KidAvatarPickerProps): React.JSX.Element {
  const current = { ...DEFAULT_AVATAR, ...value };
  const styles = useThemedKidStyles((t) => ({
    wrapper: {
      gap: t.spacing.sm,
    },
    sectionTitle: {
      fontFamily: t.fonts.bold,
      fontSize: 16,
      color: t.colors.ink,
    },
    row: {
      gap: t.spacing.sm,
      paddingVertical: t.spacing.xs,
    },
    preset: {
      width: 64,
      height: 64,
      borderRadius: t.radii.blob,
      alignItems: 'center',
      justifyContent: 'center',
      borderWidth: 3,
      borderColor: 'transparent',
    },
    presetSelected: {
      borderColor: t.colors.grape,
      transform: [{ scale: 1.08 }],
    },
    presetEmoji: {
      fontSize: 32,
    },
    companion: {
      width: 56,
      height: 56,
      borderRadius: t.radii.pill,
      backgroundColor: t.colors.surface,
      alignItems: 'center',
      justifyContent: 'center',
      borderWidth: 2,
      borderColor: 'transparent',
    },
    companionSelected: {
      borderColor: t.colors.coral,
      backgroundColor: t.colors.surfaceMuted,
    },
    companionEmoji: {
      fontSize: 28,
    },
  }));

  return (
    <View style={styles.wrapper}>
      <Text style={styles.sectionTitle}>Elige tu avatar</Text>
      <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.row}>
        {AVATAR_PRESETS.map((preset) => {
          const selected = preset.emoji === current.emoji && !current.photoUri;
          return (
            <Pressable
              key={`${preset.emoji}-${preset.backgroundColor}`}
              accessibilityRole="button"
              accessibilityState={{ selected }}
              accessibilityLabel={`Avatar ${preset.emoji}`}
              onPress={() => onChange({ ...preset, companion: current.companion ?? companion })}
              style={[styles.preset, { backgroundColor: preset.backgroundColor }, selected && styles.presetSelected]}
            >
              <Text style={styles.presetEmoji}>{preset.emoji}</Text>
            </Pressable>
          );
        })}
      </ScrollView>

      {onCompanionChange ? (
        <>
          <Text style={styles.sectionTitle}>Tu compañero</Text>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.row}>
            {COMPANION_PRESETS.map((emoji) => {
              const selected = (companion ?? current.companion) === emoji;
              return (
                <Pressable
                  key={emoji}
                  accessibilityRole="button"
                  accessibilityState={{ selected }}
                  accessibilityLabel={`Compañero ${emoji}`}
                  onPress={() => onCompanionChange(emoji)}
                  style={[styles.companion, selected && styles.companionSelected]}
                >
                  <Text style={styles.companionEmoji}>{emoji}</Text>
                </Pressable>
              );
            })}
          </ScrollView>
        </>
      ) : null}
    </View>
  );
}
