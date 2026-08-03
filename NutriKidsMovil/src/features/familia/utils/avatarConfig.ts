import type { AvatarConfig } from '../types/familia.types';

export const DEFAULT_AVATAR: AvatarConfig = {
  emoji: '🧒',
  backgroundColor: '#E8F5E9',
};

export const AVATAR_PRESETS: AvatarConfig[] = [
  { emoji: '🧒', backgroundColor: '#E8F5E9' },
  { emoji: '👧', backgroundColor: '#FCE4EC' },
  { emoji: '👦', backgroundColor: '#E3F2FD' },
  { emoji: '🦸', backgroundColor: '#FFF3E0' },
  { emoji: '🌟', backgroundColor: '#FFFDE7' },
  { emoji: '🍎', backgroundColor: '#FFEBEE' },
  { emoji: '⚽', backgroundColor: '#E0F2F1' },
  { emoji: '🎨', backgroundColor: '#F3E5F5' },
];

export function resolveAvatar(config: AvatarConfig | null | undefined): AvatarConfig {
  if (!config) {
    return DEFAULT_AVATAR;
  }
  return {
    ...DEFAULT_AVATAR,
    ...config,
  };
}

export function buildAvatarConfigFromForm(
  avatar: AvatarConfig,
  objetivoNutricional: string,
  nivelInicial: string,
): AvatarConfig {
  const config: AvatarConfig = {
    emoji: avatar.emoji ?? DEFAULT_AVATAR.emoji,
    backgroundColor: avatar.backgroundColor ?? DEFAULT_AVATAR.backgroundColor,
  };

  if (avatar.photoUri) {
    config.photoUri = avatar.photoUri;
  }

  const objetivo = objetivoNutricional.trim();
  if (objetivo) {
    config.objetivoNutricional = objetivo;
  }

  const nivel = parseInt(nivelInicial, 10);
  if (!Number.isNaN(nivel) && nivel >= 1) {
    config.nivelInicial = nivel;
  }

  return config;
}
