import React from 'react';
import { ActivityIndicator, ScrollView, Text, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import Animated, { FadeInDown } from 'react-native-reanimated';

import { KidCard } from '@features/nino/components/KidCard';
import { KidScreenBackground } from '@features/nino/components/KidScreenBackground';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import type { ChildStackParamList } from '@navigation/types';

import { HealthyActionButton } from '../components/HealthyActionButton';
import { ProgressCalendar } from '../components/ProgressCalendar';
import { useHabitCalendar } from '../hooks/useHabitCalendar';

type Props = NativeStackScreenProps<ChildStackParamList, 'HabitCalendar'>;

export function HabitCalendarScreen({ navigation }: Props): React.JSX.Element {
  const { colors, gradients } = useKidTheme();
  const styles = useThemedKidStyles((t) => ({
    content: {
      padding: t.spacing.lg,
      paddingBottom: t.spacing['3xl'],
      gap: t.spacing.md,
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
    streakBox: {
      alignItems: 'center',
      gap: 4,
    },
    streakEmoji: {
      fontSize: 32,
    },
    streakValue: {
      fontFamily: t.fonts.extraBold,
      fontSize: 22,
      color: t.colors.grape,
    },
    streakHint: {
      fontFamily: t.fonts.regular,
      fontSize: 12,
      color: t.colors.inkSoft,
    },
    navRow: {
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'space-between',
      gap: t.spacing.sm,
    },
    monthNav: {
      flex: 1,
      fontFamily: t.fonts.extraBold,
      fontSize: 16,
      color: t.colors.textOnGradient,
      textAlign: 'center',
      textTransform: 'capitalize',
    },
    loader: {
      marginVertical: t.spacing.xl,
    },
  }));

  const { year, month, days, loading, streak, goToPrevMonth, goToNextMonth } = useHabitCalendar();

  return (
    <KidScreenBackground gradient={gradients.adventure}>
      <ScrollView contentContainerStyle={styles.content}>
        <Animated.View entering={FadeInDown.springify()}>
          <Text style={styles.title}>Mi Calendario 📅</Text>
          <Text style={styles.subtitle}>Mira todo lo que has logrado</Text>
        </Animated.View>

        <KidCard style={styles.streakBox}>
          <Text style={styles.streakEmoji}>🔥</Text>
          <Text style={styles.streakValue}>{streak} días de racha</Text>
          <Text style={styles.streakHint}>¡Sigue así, campeón!</Text>
        </KidCard>

        <View style={styles.navRow}>
          <HealthyActionButton label="◀" variant="secondary" onPress={goToPrevMonth} />
          <Text style={styles.monthNav}>
            {new Date(year, month - 1).toLocaleDateString('es-MX', { month: 'long', year: 'numeric' })}
          </Text>
          <HealthyActionButton label="▶" variant="secondary" onPress={goToNextMonth} />
        </View>

        {loading ? (
          <ActivityIndicator size="large" color={colors.textOnGradient} style={styles.loader} />
        ) : (
          <ProgressCalendar year={year} month={month} days={days} />
        )}

        <HealthyActionButton
          label="Volver a mis hábitos"
          emoji="✅"
          variant="sunshine"
          onPress={() => navigation.navigate('HabitsHome')}
        />
      </ScrollView>
    </KidScreenBackground>
  );
}
