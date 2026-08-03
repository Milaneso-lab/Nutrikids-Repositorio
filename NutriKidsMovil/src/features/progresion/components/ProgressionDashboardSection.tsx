import React from 'react';
import { Text, View } from 'react-native';
import Animated, { FadeInDown } from 'react-native-reanimated';

import { CompanionMascot } from '@features/nino/components/CompanionMascot';
import { ExperienceBar } from '@features/nino/components/ExperienceBar';
import { KidAchievementCard } from '@features/nino/components/KidAchievementCard';
import { KidCard } from '@features/nino/components/KidCard';
import { KidProgressCard } from '@features/nino/components/KidProgressCard';
import { LevelBadge } from '@features/nino/components/LevelBadge';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import type { ProgressionSnapshot } from '@features/progresion/types/progression.types';

interface ProgressionDashboardSectionProps {
  snapshot: ProgressionSnapshot;
  childName: string;
  onStartAdventure?: () => void;
}

export function ProgressionDashboardSection({
  snapshot,
  childName,
}: ProgressionDashboardSectionProps): React.JSX.Element {
  const styles = useThemedKidStyles((t) => ({
    wrapper: {
      gap: t.spacing.md,
    },
    heroCard: {
      gap: t.spacing.md,
      alignItems: 'center',
    },
    levelRow: {
      alignItems: 'center',
      gap: 4,
    },
    levelHint: {
      fontFamily: t.fonts.medium,
      fontSize: 12,
      color: t.colors.inkSoft,
    },
    statsGrid: {
      flexDirection: 'row',
      flexWrap: 'wrap',
      gap: t.spacing.sm,
    },
    statBubble: {
      width: '47%',
      backgroundColor: t.colors.surfaceElevated,
      borderRadius: t.radii.card,
      padding: t.spacing.sm,
      alignItems: 'center',
      gap: 2,
      minHeight: 80,
    },
    statEmoji: {
      fontSize: 22,
    },
    statValue: {
      fontFamily: t.fonts.extraBold,
      fontSize: 20,
      color: t.colors.ink,
    },
    statLabel: {
      fontFamily: t.fonts.medium,
      fontSize: 11,
      color: t.colors.inkSoft,
    },
    recentSection: {
      gap: t.spacing.xs,
    },
    sectionTitle: {
      fontFamily: t.fonts.extraBold,
      fontSize: 18,
      color: t.colors.textOnGradient,
    },
    unlockBox: {
      backgroundColor: t.colors.surfaceElevated,
      borderRadius: t.radii.card,
      padding: t.spacing.md,
      gap: 4,
    },
    unlockTitle: {
      fontFamily: t.fonts.bold,
      fontSize: 14,
      color: t.colors.grape,
    },
    unlockItem: {
      fontFamily: t.fonts.medium,
      fontSize: 13,
      color: t.colors.ink,
    },
  }));

  const dailyMission = snapshot.missions.daily[0];
  const recentAchievement = snapshot.achievements.filter((a) => a.unlocked).slice(-1)[0];

  const stats = [
    { emoji: '🪙', value: String(snapshot.coins.balance), label: 'monedas' },
    { emoji: '⚡', value: `${snapshot.energy.current}`, label: 'energía' },
    { emoji: '🔥', value: String(snapshot.streak.current), label: 'racha' },
    { emoji: '⭐', value: String(snapshot.xp.total), label: 'XP total' },
  ];

  return (
    <View style={styles.wrapper}>
      <Animated.View entering={FadeInDown.springify()}>
        <KidCard style={styles.heroCard}>
          <View style={styles.levelRow}>
            <LevelBadge level={snapshot.xp.currentLevel} size="lg" />
            <Text style={styles.levelHint}>Nv. {snapshot.xp.nextLevel} próximo</Text>
          </View>
          <ExperienceBar
            current={snapshot.xp.xpInLevel}
            max={snapshot.xp.xpToNextLevel}
            label="Experiencia"
          />
          <CompanionMascot
            emoji={snapshot.pet.emoji}
            message={`${childName}, tu ${snapshot.pet.name} está en fase ${snapshot.pet.stage}. ¡Vamos!`}
          />
        </KidCard>
      </Animated.View>

      {dailyMission ? (
        <KidProgressCard
          index={0}
          emoji={dailyMission.emoji}
          title="Objetivo del día"
          subtitle={`${dailyMission.title} (${dailyMission.progress}/${dailyMission.target})`}
          progress={dailyMission.target > 0 ? dailyMission.progress / dailyMission.target : 0}
        />
      ) : null}

      <View style={styles.statsGrid}>
        {stats.map((stat) => (
          <View key={stat.label} style={styles.statBubble} accessibilityLabel={`${stat.label}: ${stat.value}`}>
            <Text style={styles.statEmoji}>{stat.emoji}</Text>
            <Text style={styles.statValue}>{stat.value}</Text>
            <Text style={styles.statLabel}>{stat.label}</Text>
          </View>
        ))}
      </View>

      {recentAchievement ? (
        <View style={styles.recentSection}>
          <Text style={styles.sectionTitle}>Último logro</Text>
          <KidAchievementCard
            emoji={recentAchievement.emoji}
            title={recentAchievement.name}
            description={recentAchievement.description}
          />
        </View>
      ) : null}

      {snapshot.nextLevelUnlocks.length > 0 ? (
        <View style={styles.unlockBox}>
          <Text style={styles.unlockTitle}>Próximo desbloqueo</Text>
          <Text style={styles.unlockItem}>
            {snapshot.nextLevelUnlocks[0]?.emoji} {snapshot.nextLevelUnlocks[0]?.title} (Nv.{' '}
            {snapshot.nextLevelUnlocks[0]?.level})
          </Text>
        </View>
      ) : null}
    </View>
  );
}
