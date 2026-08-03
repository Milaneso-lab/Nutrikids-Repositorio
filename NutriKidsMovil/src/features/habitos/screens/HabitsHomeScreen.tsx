import React from 'react';
import {
  ActivityIndicator,
  RefreshControl,
  ScrollView,
  Text,
  View,
} from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import Animated, { FadeInDown } from 'react-native-reanimated';

import { KidScreenBackground } from '@features/nino/components/KidScreenBackground';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import { ProgressionHud } from '@features/progresion/components/ProgressionHud';
import { useProgression } from '@features/progresion/hooks/useProgression';
import type { ChildStackParamList } from '@navigation/types';

import { DailyHabitTracker } from '../components/DailyHabitTracker';
import { HealthyActionButton } from '../components/HealthyActionButton';
import { PetReactionCard } from '../components/PetReactionCard';
import { WeeklyProgressCard } from '../components/WeeklyProgressCard';
import { useHabits } from '../hooks/useHabits';

type Props = NativeStackScreenProps<ChildStackParamList, 'HabitsHome'>;

export function HabitsHomeScreen({ navigation }: Props): React.JSX.Element {
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
      fontSize: 16,
      color: t.colors.textOnGradient,
    },
    title: {
      fontFamily: t.fonts.extraBold,
      fontSize: 26,
      color: t.colors.textOnGradient,
    },
    subtitle: {
      fontFamily: t.fonts.medium,
      fontSize: 14,
      color: t.colors.textOnGradientMuted,
      marginTop: 4,
      marginBottom: t.spacing.sm,
    },
    actions: {
      gap: t.spacing.sm,
      marginTop: t.spacing.xs,
    },
    error: {
      fontFamily: t.fonts.medium,
      fontSize: 13,
      color: t.colors.textOnGradient,
      textAlign: 'center',
    },
  }));

  const { snapshot } = useProgression();
  const {
    daily,
    statistics,
    loading,
    togglingId,
    error,
    petReaction,
    dailyTarget,
    motivationMessage,
    refresh,
    toggleHabit,
  } = useHabits();

  if (loading && daily.entries.length === 0) {
    return (
      <KidScreenBackground gradient={gradients.adventure}>
        <View style={styles.center}>
          <ActivityIndicator size="large" color={colors.textOnGradient} />
          <Text style={styles.loadingText}>Preparando tus hábitos…</Text>
        </View>
      </KidScreenBackground>
    );
  }

  return (
    <KidScreenBackground gradient={gradients.adventure}>
      <ScrollView
        contentContainerStyle={styles.content}
        refreshControl={
          <RefreshControl refreshing={loading} onRefresh={refresh} tintColor={colors.textOnGradient} />
        }
      >
        <Animated.View entering={FadeInDown.springify()}>
          <Text style={styles.title}>Mis Hábitos Saludables 🌱</Text>
          <Text style={styles.subtitle}>Cada acción te hace más fuerte</Text>
        </Animated.View>

        {snapshot ? <ProgressionHud snapshot={snapshot} /> : null}

        <PetReactionCard reaction={petReaction} />

        <DailyHabitTracker
          daily={daily}
          dailyTarget={dailyTarget}
          motivationMessage={motivationMessage}
          togglingId={togglingId}
          onToggle={(id) => void toggleHabit(id)}
        />

        {statistics ? <WeeklyProgressCard weekly={statistics.weekly} /> : null}

        <View style={styles.actions}>
          <HealthyActionButton
            label="Ver calendario"
            emoji="📅"
            variant="secondary"
            onPress={() => navigation.navigate('HabitCalendar')}
          />
          <HealthyActionButton
            label="Mis estadísticas"
            emoji="📊"
            variant="sunshine"
            onPress={() => navigation.navigate('HabitStatistics')}
          />
        </View>

        {error ? <Text style={styles.error}>{error}</Text> : null}
      </ScrollView>
    </KidScreenBackground>
  );
}
