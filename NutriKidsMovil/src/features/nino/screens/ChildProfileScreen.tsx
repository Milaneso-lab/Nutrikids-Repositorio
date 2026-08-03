import React from 'react';
import { ActivityIndicator, ScrollView, Text, View } from 'react-native';
import { CompositeScreenProps } from '@react-navigation/native';
import { BottomTabScreenProps } from '@react-navigation/bottom-tabs';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import Animated, { FadeInDown } from 'react-native-reanimated';

import type { ChildStackParamList, ChildTabParamList } from '@navigation/types';

import { CompanionMascot } from '../components/CompanionMascot';
import { ExperienceBar } from '../components/ExperienceBar';
import { KidAchievementCard } from '../components/KidAchievementCard';
import { KidActionButton } from '../components/KidActionButton';
import { KidAvatarDisplay } from '../components/KidAvatarDisplay';
import { KidCard } from '../components/KidCard';
import { KidScreenBackground } from '../components/KidScreenBackground';
import { LevelBadge } from '../components/LevelBadge';
import { useChildProfileView } from '../hooks/useChildProfileView';
import { useThemedKidStyles } from '../hooks/useThemedKidStyles';
import { useKidTheme } from '../providers/KidThemeProvider';

type Props = CompositeScreenProps<
  BottomTabScreenProps<ChildTabParamList, 'Perfil'>,
  NativeStackScreenProps<ChildStackParamList>
>;

export function ChildProfileScreen({ navigation }: Props): React.JSX.Element {
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
      gap: t.spacing.sm,
    },
    loadingText: {
      fontFamily: t.fonts.semiBold,
      fontSize: 15,
      color: t.colors.textOnGradient,
    },
    error: {
      fontFamily: t.fonts.bold,
      color: t.colors.textOnGradient,
    },
    hero: {
      alignItems: 'center',
      gap: t.spacing.sm,
      paddingVertical: t.spacing.md,
    },
    name: {
      fontFamily: t.fonts.extraBold,
      fontSize: 30,
      color: t.colors.textOnGradient,
    },
    meta: {
      fontFamily: t.fonts.medium,
      fontSize: 15,
      color: t.colors.textOnGradientMuted,
    },
    card: {
      gap: t.spacing.xs,
    },
    cardTitle: {
      fontFamily: t.fonts.bold,
      fontSize: 16,
      color: t.colors.ink,
    },
    cardValue: {
      fontFamily: t.fonts.extraBold,
      fontSize: 22,
      color: t.colors.grape,
    },
    cardHint: {
      fontFamily: t.fonts.regular,
      fontSize: 11,
      color: t.colors.inkSoft,
    },
    sectionTitle: {
      fontFamily: t.fonts.extraBold,
      fontSize: 18,
      color: t.colors.textOnGradient,
      marginTop: t.spacing.xs,
    },
    list: {
      gap: t.spacing.sm,
    },
    emptyHint: {
      fontFamily: t.fonts.medium,
      fontSize: 13,
      color: t.colors.textOnGradientMuted,
      textAlign: 'center',
      paddingVertical: t.spacing.sm,
    },
  }));

  const {
    activeChild,
    age,
    snapshot,
    progressionLoading,
    weekly,
    badges,
    achievements,
  } = useChildProfileView();

  if (!activeChild) {
    return (
      <KidScreenBackground gradient={gradients.profile}>
        <View style={styles.center}>
          <Text style={styles.error}>No hay sesión activa</Text>
        </View>
      </KidScreenBackground>
    );
  }

  if (progressionLoading && !snapshot) {
    return (
      <KidScreenBackground gradient={gradients.profile}>
        <View style={styles.center}>
          <ActivityIndicator size="large" color={colors.textOnGradient} />
          <Text style={styles.loadingText}>Cargando tu progreso…</Text>
        </View>
      </KidScreenBackground>
    );
  }

  const level = snapshot?.xp.currentLevel ?? activeChild.nivel;
  const streak = snapshot?.streak.current ?? 0;
  const streakBest = snapshot?.streak.best ?? 0;
  const pet = snapshot?.pet;

  return (
    <KidScreenBackground gradient={gradients.profile}>
      <ScrollView contentContainerStyle={styles.content}>
        <Animated.View entering={FadeInDown.springify()} style={styles.hero}>
          <KidAvatarDisplay avatarConfig={activeChild.avatar_config} size="hero" />
          <Text style={styles.name}>{activeChild.nombre}</Text>
          <Text style={styles.meta}>
            {age != null ? `${age} años` : 'Edad no disponible'} · Nivel {level}
          </Text>
          <LevelBadge level={level} size="lg" />
          {snapshot ? (
            <ExperienceBar
              current={snapshot.xp.xpInLevel}
              max={snapshot.xp.xpToNextLevel}
              label="Experiencia"
            />
          ) : null}
        </Animated.View>

        <KidCard style={styles.card}>
          <Text style={styles.cardTitle}>🔥 Racha de días</Text>
          <Text style={styles.cardValue}>{streak} días seguidos</Text>
          <Text style={styles.cardHint}>Tu récord: {streakBest} días</Text>
        </KidCard>

        <KidCard style={styles.card}>
          <Text style={styles.cardTitle}>📅 Progreso semanal</Text>
          <Text style={styles.cardValue}>
            {weekly.daysActive} de {weekly.daysTotal} días activos
          </Text>
          <ExperienceBar
            current={weekly.daysActive}
            max={weekly.daysTotal}
            label="Esta semana"
            showValues={false}
          />
        </KidCard>

        {pet ? (
          <CompanionMascot
            emoji={pet.emoji}
            message={`¡Hola ${activeChild.nombre}! Soy ${pet.name}, tu compañero en fase ${pet.stage}.`}
          />
        ) : (
          <CompanionMascot
            emoji={activeChild.companion ?? '🦊'}
            message={`¡Hola ${activeChild.nombre}! Soy tu compañero de aventuras.`}
          />
        )}

        <Text style={styles.sectionTitle}>Insignias</Text>
        <View style={styles.list}>
          {badges.length > 0 ? (
            badges.map((badge) => (
              <KidAchievementCard
                key={badge.id}
                emoji={badge.emoji}
                title={badge.title}
                locked={badge.locked}
              />
            ))
          ) : (
            <Text style={styles.emptyHint}>Completa misiones para desbloquear insignias</Text>
          )}
        </View>

        <Text style={styles.sectionTitle}>Logros</Text>
        <View style={styles.list}>
          {achievements.length > 0 ? (
            achievements.map((item) => (
              <KidAchievementCard
                key={item.id}
                emoji={item.emoji}
                title={item.title}
                description={item.description}
                locked={item.locked}
              />
            ))
          ) : (
            <Text style={styles.emptyHint}>Tus logros aparecerán aquí</Text>
          )}
        </View>

        <KidActionButton
          label="Cambiar mi avatar"
          emoji="🎨"
          variant="secondary"
          onPress={() => navigation.navigate('AvatarEditor')}
        />
      </ScrollView>
    </KidScreenBackground>
  );
}
