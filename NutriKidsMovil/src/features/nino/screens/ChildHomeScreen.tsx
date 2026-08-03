import React from 'react';
import {
  ActivityIndicator,
  RefreshControl,
  ScrollView,
  Text,
  View,
} from 'react-native';
import { CompositeScreenProps } from '@react-navigation/native';
import { BottomTabScreenProps } from '@react-navigation/bottom-tabs';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import Animated, { FadeInDown } from 'react-native-reanimated';

import { KidActionButton } from '@features/nino/components/KidActionButton';
import { ChildHeaderProfileMenu } from '@features/nino/components/ChildHeaderProfileMenu';
import { KidScreenBackground } from '@features/nino/components/KidScreenBackground';
import { useChildHome } from '@features/nino/hooks/useChildHome';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import { ProgressionDashboardSection } from '@features/progresion/components/ProgressionDashboardSection';
import { ProgressionHud } from '@features/progresion/components/ProgressionHud';
import { useProgression } from '@features/progresion/hooks/useProgression';
import type { ChildStackParamList, ChildTabParamList } from '@navigation/types';

type Props = CompositeScreenProps<
  BottomTabScreenProps<ChildTabParamList, 'Inicio'>,
  NativeStackScreenProps<ChildStackParamList>
>;

export function ChildHomeScreen({ navigation }: Props): React.JSX.Element {
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
    errorText: {
      fontFamily: t.fonts.bold,
      fontSize: 16,
      color: t.colors.textOnGradient,
      textAlign: 'center',
      paddingHorizontal: t.spacing.lg,
    },
    header: {
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'space-between',
      gap: t.spacing.md,
    },
    headerText: {
      flex: 1,
      gap: 4,
    },
    greeting: {
      fontFamily: t.fonts.extraBold,
      fontSize: 28,
      color: t.colors.textOnGradient,
    },
    subGreeting: {
      fontFamily: t.fonts.medium,
      fontSize: 15,
      color: t.colors.textOnGradientMuted,
    },
    cta: {
      marginTop: t.spacing.xs,
    },
    warnText: {
      fontFamily: t.fonts.medium,
      fontSize: 12,
      color: t.colors.textOnGradient,
      textAlign: 'center',
      opacity: 0.85,
    },
  }));

  const { child, loading, refreshing, error, refresh } = useChildHome();
  const { snapshot, loading: progLoading } = useProgression();

  const isLoading = (loading || progLoading) && !child;

  if (isLoading) {
    return (
      <KidScreenBackground gradient={gradients.home}>
        <View style={styles.center}>
          <ActivityIndicator size="large" color={colors.textOnGradient} />
          <Text style={styles.loadingText}>Preparando tu aventura…</Text>
        </View>
      </KidScreenBackground>
    );
  }

  if (!child || !snapshot) {
    return (
      <KidScreenBackground gradient={gradients.home}>
        <View style={styles.center}>
          <Text style={styles.errorText}>{error ?? 'No encontramos tu perfil'}</Text>
        </View>
      </KidScreenBackground>
    );
  }

  return (
    <KidScreenBackground gradient={gradients.home}>
      <ScrollView
        contentContainerStyle={styles.content}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={refresh} tintColor={colors.textOnGradient} />
        }
      >
        <Animated.View entering={FadeInDown.springify()} style={styles.header}>
          <View style={styles.headerText}>
            <Text style={styles.greeting}>¡Hola, {child.nombre}! 👋</Text>
            <Text style={styles.subGreeting}>Tu aventura nutricional continúa</Text>
          </View>
          <ChildHeaderProfileMenu avatarConfig={child.avatar_config} childName={child.nombre} />
        </Animated.View>

        <ProgressionHud snapshot={snapshot} />

        <ProgressionDashboardSection snapshot={snapshot} childName={child.nombre} />

        <KidActionButton
          label="¡Comenzar aventura del día!"
          emoji="🚀"
          variant="sunshine"
          onPress={() => navigation.navigate('HabitsHome')}
          style={styles.cta}
        />

        <KidActionButton
          label="Ver mi calendario"
          emoji="📅"
          variant="secondary"
          onPress={() => navigation.navigate('HabitCalendar')}
        />

        {error ? <Text style={styles.warnText}>{error}</Text> : null}
      </ScrollView>
    </KidScreenBackground>
  );
}
