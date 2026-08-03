import { ENERGY_CONFIG, PET_EVOLUTION_THRESHOLDS } from '../../config/progression.config';
import type { EnergyState, PetEvolutionStage, PetState } from '../../types/progression.types';

function todayIsoDate(): string {
  return new Date().toISOString().slice(0, 10);
}

export function createInitialEnergy(): EnergyState {
  return {
    current: ENERGY_CONFIG.max,
    max: ENERGY_CONFIG.max,
    lastRegenDate: todayIsoDate(),
  };
}

export function regenerateDailyEnergy(energy: EnergyState): EnergyState {
  const today = todayIsoDate();
  if (energy.lastRegenDate === today) {
    return energy;
  }
  return {
    ...energy,
    current: energy.max,
    lastRegenDate: today,
  };
}

export function consumeEnergy(energy: EnergyState, amount: number): { energy: EnergyState; success: boolean } {
  if (energy.current < amount) {
    return { energy, success: false };
  }
  return {
    energy: { ...energy, current: energy.current - amount },
    success: true,
  };
}

export function resolvePetStage(level: number): PetEvolutionStage {
  if (level >= PET_EVOLUTION_THRESHOLDS.hero) {
    return 'hero';
  }
  if (level >= PET_EVOLUTION_THRESHOLDS.teen) {
    return 'teen';
  }
  if (level >= PET_EVOLUTION_THRESHOLDS.kid) {
    return 'kid';
  }
  if (level >= PET_EVOLUTION_THRESHOLDS.baby) {
    return 'baby';
  }
  return 'egg';
}

export function buildPetState(companionEmoji: string, level: number, existing?: Partial<PetState>): PetState {
  const stage = resolvePetStage(level);
  const thresholds = Object.values(PET_EVOLUTION_THRESHOLDS).filter((v) => v > 0);
  const nextThreshold = thresholds.find((t) => t > level) ?? thresholds[thresholds.length - 1] ?? 1;
  const prevThreshold =
    [...thresholds].reverse().find((t) => t <= level) ?? PET_EVOLUTION_THRESHOLDS.egg;
  const span = Math.max(1, nextThreshold - prevThreshold);
  const evolutionProgress = Math.min(1, (level - prevThreshold) / span);

  return {
    companionId: existing?.companionId ?? 'default',
    emoji: companionEmoji,
    name: existing?.name ?? 'Compañero',
    stage,
    mood: existing?.mood ?? 'happy',
    evolutionProgress,
    equippedAccessoryIds: existing?.equippedAccessoryIds ?? [],
    xpFed: existing?.xpFed ?? 0,
  };
}

export function getNextLevelUnlocks(level: number) {
  return [
    {
      level: level + 1,
      title: 'Nuevo accesorio',
      emoji: '🎒',
      description: 'Desbloquea un accesorio para tu avatar',
    },
    {
      level: level + 2,
      title: 'Evolución mascota',
      emoji: '🦊',
      description: 'Tu compañero crece contigo',
    },
  ];
}
