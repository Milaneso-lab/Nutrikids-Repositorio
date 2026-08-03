import React from 'react';
import { Image, Pressable, StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';
import { AppText } from '@shared/components/ui/Text';
import { formatPoints } from '@shared/utils/format';

import { useParentTheme } from '../providers/ParentThemeProvider';
import type { NinoWithPuntos } from '../types/familia.types';
import { formatAgeLabel } from '../utils/age';
import { resolveAvatar } from '../utils/avatarConfig';
import { ProgressIndicator } from './ProgressIndicator';

interface ChildCardProps {
  nino: NinoWithPuntos;
  onPress: () => void;
  onEdit?: () => void;
  onPlayAsChild?: () => void;
}

export function ChildCard({ nino, onPress, onEdit, onPlayAsChild }: ChildCardProps): React.JSX.Element {
  const { colors } = useParentTheme();
  const avatar = resolveAvatar(nino.avatar_config);
  const nivel = nino.puntos?.nivel_actual ?? 1;
  const puntos = nino.puntos?.puntos_totales ?? 0;
  const progress = Math.min((puntos % 100) / 100, 1);

  return (
    <Pressable
      accessibilityRole="button"
      accessibilityLabel={`Ver perfil de ${nino.nombre}`}
      onPress={onPress}
      style={({ pressed }) => [
        styles.card,
        {
          backgroundColor: colors.surface,
          borderColor: colors.border,
          shadowColor: colors.shadow,
        },
        pressed && styles.pressed,
      ]}
    >
      <View style={[styles.accent, { backgroundColor: colors.cardAccent }]} />
      <View style={styles.row}>
        <View style={[styles.avatar, { backgroundColor: avatar.backgroundColor }]}>
          {avatar.photoUri ? (
            <Image source={{ uri: avatar.photoUri }} style={styles.photo} accessibilityIgnoresInvertColors />
          ) : (
            <AppText style={styles.emoji}>{avatar.emoji}</AppText>
          )}
        </View>

        <View style={styles.content}>
          <AppText variant="h3" style={{ color: colors.textPrimary }}>
            {nino.nombre}
          </AppText>
          <AppText variant="caption" style={{ color: colors.textSecondary }}>
            {formatAgeLabel(nino.fecha_nacimiento)} · Nivel {nivel}
          </AppText>
          <AppText variant="caption" style={{ color: colors.textSecondary }}>
            {formatPoints(puntos)} puntos
          </AppText>
          <ProgressIndicator progress={progress} label={`Progreso al nivel ${nivel + 1}`} />
          {onPlayAsChild ? (
            <Pressable
              accessibilityRole="button"
              accessibilityLabel={`Entrar como ${nino.nombre}`}
              onPress={(event) => {
                event.stopPropagation();
                onPlayAsChild();
              }}
              style={[styles.playButton, { backgroundColor: colors.accentSoft }]}
            >
              <AppText variant="caption" style={[styles.playLabel, { color: colors.accent }]}>
                🎮 Modo niño
              </AppText>
            </Pressable>
          ) : null}
        </View>

        {onEdit ? (
          <Pressable
            accessibilityRole="button"
            accessibilityLabel={`Editar perfil de ${nino.nombre}`}
            onPress={(event) => {
              event.stopPropagation();
              onEdit();
            }}
            hitSlop={8}
            style={[styles.editButton, { backgroundColor: colors.surfaceMuted }]}
          >
            <AppText variant="caption" style={[styles.editLabel, { color: colors.accent }]}>
              Editar
            </AppText>
          </Pressable>
        ) : null}
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  card: {
    borderRadius: theme.radii.xl,
    padding: theme.spacing.md,
    borderWidth: 1,
    overflow: 'hidden',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.08,
    shadowRadius: 16,
    elevation: 3,
  },
  accent: {
    position: 'absolute',
    left: 0,
    top: 0,
    bottom: 0,
    width: 4,
  },
  pressed: {
    opacity: 0.94,
    transform: [{ scale: 0.995 }],
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: theme.spacing.md,
    paddingLeft: theme.spacing.xs,
  },
  avatar: {
    width: 58,
    height: 58,
    borderRadius: theme.radii.pill,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
  },
  photo: {
    width: '100%',
    height: '100%',
  },
  emoji: {
    fontSize: 28,
  },
  content: {
    flex: 1,
    gap: theme.spacing.xxs,
  },
  editButton: {
    minHeight: 36,
    minWidth: 56,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: theme.radii.pill,
    paddingHorizontal: theme.spacing.sm,
  },
  editLabel: {
    fontFamily: theme.fonts.semiBold,
  },
  playButton: {
    marginTop: theme.spacing.xxs,
    alignSelf: 'flex-start',
    minHeight: 32,
    justifyContent: 'center',
    borderRadius: theme.radii.pill,
    paddingHorizontal: theme.spacing.sm,
  },
  playLabel: {
    fontFamily: theme.fonts.semiBold,
  },
});
