import React from 'react';
import { ActivityIndicator, ScrollView, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import Animated, { FadeInDown } from 'react-native-reanimated';

import { KidCard } from '@features/nino/components/KidCard';
import { KidScreenBackground } from '@features/nino/components/KidScreenBackground';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import { useProgression } from '@features/progresion/hooks/useProgression';
import type { ChildStackParamList } from '@navigation/types';

import { HealthyActionButton } from '../components/HealthyActionButton';
import { StatisticsCard } from '../components/StatisticsCard';
import { WeeklyProgressCard } from '../components/WeeklyProgressCard';
import { useHabitStatistics } from '../hooks/useHabits';

type Props = NativeStackScreenProps<ChildStackParamList, 'HabitStatistics'>;

export function HabitStatisticsScreen({ navigation }: Props): React.JSX.Element {
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
      marginBottom: t.spacing.sm,
    },
    grid: {
      flexDirection: 'row',
      flexWrap: 'wrap',
      gap: t.spacing.sm,
    },
    encouragement: {
      gap: 0,
    },
    encouragementText: {
      fontFamily: t.fonts.medium,
      fontSize: 14,
      color: t.colors.ink,
      textAlign: 'center',
    },
  }));

  const { statistics, loading, childName } = useHabitStatistics();
  const { snapshot } = useProgression();

  if (loading && !statistics) {
    return (
      <KidScreenBackground gradient={gradients.profile}>
        <View style={styles.center}>
          <ActivityIndicator size="large" color={colors.textOnGradient} />
        </View>
      </KidScreenBackground>
    );
  }

  return (
    <KidScreenBackground gradient={gradients.profile}>
      <ScrollView contentContainerStyle={styles.content}>
        <Animated.View entering={FadeInDown.springify()}>
          <Text style={styles.title}>Mis Estadísticas 📊</Text>
          <Text style={styles.subtitle}>El progreso de {childName}</Text>
        </Animated.View>

        <View style={styles.grid}>
          <StatisticsCard
            emoji="✅"
            label="Hábitos completados"
            value={statistics?.totalCompletions ?? 0}
            accent={colors.mint}
          />
          <StatisticsCard
            emoji="🔥"
            label="Racha actual"
            value={statistics?.currentStreak ?? 0}
            hint="días seguidos"
            accent={colors.coral}
          />
          <StatisticsCard
            emoji="📅"
            label="Días activos (mes)"
            value={statistics?.monthly.daysWithActivity ?? 0}
            accent={colors.sky}
          />
          <StatisticsCard
            emoji="🪙"
            label="Monedas"
            value={snapshot?.coins.balance ?? 0}
            accent={colors.sunshine}
          />
        </View>

        {statistics ? <WeeklyProgressCard weekly={statistics.weekly} /> : null}

        <KidCard style={styles.encouragement}>
          <Text style={styles.encouragementText}>
            Recuerda: no se trata de ser perfecto, sino de cuidarte con alegría 💚
          </Text>
        </KidCard>

        <HealthyActionButton
          label="Volver a mis hábitos"
          emoji="🌱"
          variant="sunshine"
          onPress={() => navigation.navigate('HabitsHome')}
        />
      </ScrollView>
    </KidScreenBackground>
  );
}
