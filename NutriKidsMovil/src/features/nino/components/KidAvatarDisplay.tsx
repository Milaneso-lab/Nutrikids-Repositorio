import React from 'react';
import { Image, Text, View } from 'react-native';

import type { AvatarConfig } from '@features/familia/types/familia.types';
import { resolveAvatar } from '@features/familia/utils/avatarConfig';

import { useThemedKidStyles } from '../hooks/useThemedKidStyles';

interface KidAvatarDisplayProps {
  avatarConfig: AvatarConfig | null;
  size?: 'sm' | 'md' | 'lg' | 'hero';
  showRing?: boolean;
}

const SIZE_MAP = {
  sm: 56,
  md: 80,
  lg: 112,
  hero: 140,
} as const;

export function KidAvatarDisplay({
  avatarConfig,
  size = 'md',
  showRing = true,
}: KidAvatarDisplayProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    wrapper: {
      alignItems: 'center',
      justifyContent: 'center',
    },
    ring: {
      borderWidth: 4,
      borderColor: t.colors.surface,
      ...t.shadow.card,
    },
    avatar: {
      alignItems: 'center',
      justifyContent: 'center',
      overflow: 'hidden',
    },
    photo: {
      width: '100%',
      height: '100%',
    },
    emoji: {
      textAlign: 'center',
    },
  }));

  const avatar = resolveAvatar(avatarConfig);
  const dimension = SIZE_MAP[size];
  const emojiSize = Math.round(dimension * 0.48);

  return (
    <View
      style={[
        styles.wrapper,
        showRing && styles.ring,
        { width: dimension + 14, height: dimension + 14, borderRadius: (dimension + 14) / 2 },
      ]}
    >
      <View
        style={[
          styles.avatar,
          {
            width: dimension,
            height: dimension,
            borderRadius: dimension / 2,
            backgroundColor: avatar.backgroundColor,
          },
        ]}
      >
        {avatar.photoUri ? (
          <Image source={{ uri: avatar.photoUri }} style={styles.photo} accessibilityIgnoresInvertColors />
        ) : (
          <Text style={[styles.emoji, { fontSize: emojiSize }]} accessibilityLabel={`Avatar ${avatar.emoji}`}>
            {avatar.emoji}
          </Text>
        )}
      </View>
    </View>
  );
}
