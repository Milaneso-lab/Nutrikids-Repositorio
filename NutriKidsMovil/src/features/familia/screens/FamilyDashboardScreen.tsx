import React, { useCallback } from 'react';
import {
  ActivityIndicator,
  RefreshControl,
  ScrollView,
  StyleSheet,
  View,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useFocusEffect } from '@react-navigation/native';

import { theme } from '@core/theme';
import { Button } from '@shared/components/ui/Button';
import { ErrorMessage } from '@shared/components/ui/ErrorMessage';
import { AppText } from '@shared/components/ui/Text';
import { useAppStore } from '@state/stores/appStore';
import { useEnterChildMode } from '@features/nino/hooks/useEnterChildMode';
import type { FamilyStackParamList } from '@navigation/types';

import { ChildCard } from '../components/ChildCard';
import { EmptyState } from '../components/EmptyState';
import { FamilySummaryCard } from '../components/FamilySummaryCard';
import { ParentScreen } from '../components/ParentScreen';
import { QuickActionButton } from '../components/QuickActionButton';
import { useParentTheme } from '../providers/ParentThemeProvider';
import { useNinosList } from '../hooks/useNinosList';

type Props = NativeStackScreenProps<FamilyStackParamList, 'FamilyDashboard'>;

export function FamilyDashboardScreen({ navigation }: Props): React.JSX.Element {
  const user = useAppStore((state) => state.user);
  const { colors } = useParentTheme();
  const { ninos, summary, loading, refreshing, error, refreshError, refresh, retry } = useNinosList();
  const { enterWithNino } = useEnterChildMode();

  useFocusEffect(
    useCallback(() => {
      refresh();
    }, [refresh]),
  );

  const greeting = user ? `Hola, ${user.nombre}` : 'Centro familiar';
  const hasCachedData = ninos.length > 0;
  const showFatalError = Boolean(error) && !hasCachedData;
  const showContent = hasCachedData || (!loading && !showFatalError);

  return (
    <ParentScreen>
      <ScrollView
        contentContainerStyle={styles.content}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={refresh} tintColor={colors.accent} />
        }
        showsVerticalScrollIndicator={false}
      >
        <LinearGradient colors={[...colors.heroGradient]} style={styles.hero}>
          <View style={styles.heroBadge}>
            <AppText variant="caption" style={styles.heroBadgeText}>
              Panel familiar
            </AppText>
          </View>
          <AppText variant="h1" style={styles.heroTitle}>
            {greeting}
          </AppText>
          <AppText variant="bodySmall" style={styles.heroSubtitle}>
            Administra perfiles, progreso y aventuras de tus hijos
          </AppText>
        </LinearGradient>

        {loading && !hasCachedData ? (
          <View style={[styles.loadingBox, { backgroundColor: colors.surface }]}>
            <ActivityIndicator size="large" color={colors.accent} />
            <AppText variant="bodySmall" style={{ color: colors.textSecondary }}>
              Cargando perfiles…
            </AppText>
          </View>
        ) : null}

        {refreshError ? (
          <View style={[styles.warningBox, { backgroundColor: colors.accentSoft, borderColor: colors.accentMuted }]}>
            <ErrorMessage message={refreshError} />
            <Button label="Reintentar" variant="secondary" onPress={refresh} />
          </View>
        ) : null}

        {showFatalError ? (
          <View style={styles.errorBox}>
            <ErrorMessage message={error!} />
            <Button label="Reintentar" variant="secondary" onPress={retry} />
          </View>
        ) : null}

        {showContent ? (
          <View style={styles.body}>
            <FamilySummaryCard summary={summary} />

            <View style={styles.quickActions}>
              <QuickActionButton
                icon="➕"
                label="Registrar hijo"
                variant="primary"
                onPress={() => navigation.navigate('ChildForm', {})}
              />
              <QuickActionButton icon="🔄" label="Actualizar" onPress={refresh} />
            </View>

            <View style={styles.sectionHeader}>
              <AppText variant="h3" style={{ color: colors.textPrimary }}>
                Tus hijos
              </AppText>
              <AppText variant="caption" style={{ color: colors.textSecondary }}>
                {summary.totalHijos} registrados
              </AppText>
            </View>

            {ninos.length === 0 ? (
              <EmptyState
                title="Aún no hay perfiles"
                description="Registra a tu primer hijo para comenzar a seguir su progreso nutricional y de gamificación."
                actionLabel="Registrar hijo"
                onAction={() => navigation.navigate('ChildForm', {})}
              />
            ) : (
              <View style={styles.list}>
                {ninos.map((nino) => (
                  <ChildCard
                    key={nino.id}
                    nino={nino}
                    onPress={() => navigation.navigate('ChildProfile', { ninoId: nino.id })}
                    onEdit={() => navigation.navigate('ChildForm', { ninoId: nino.id })}
                    onPlayAsChild={() => void enterWithNino(nino)}
                  />
                ))}
              </View>
            )}
          </View>
        ) : null}
      </ScrollView>
    </ParentScreen>
  );
}

const styles = StyleSheet.create({
  content: {
    paddingBottom: theme.spacing['3xl'],
  },
  hero: {
    paddingHorizontal: theme.spacing.lg,
    paddingTop: theme.spacing.md,
    paddingBottom: theme.spacing.xl,
    borderBottomLeftRadius: theme.radii.xl,
    borderBottomRightRadius: theme.radii.xl,
    gap: theme.spacing.xs,
  },
  heroBadge: {
    alignSelf: 'flex-start',
    backgroundColor: 'rgba(255,255,255,0.18)',
    borderRadius: theme.radii.pill,
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: theme.spacing.xxs,
  },
  heroBadgeText: {
    color: '#FFFFFF',
    fontFamily: theme.fonts.semiBold,
  },
  heroTitle: {
    color: '#FFFFFF',
    fontFamily: theme.fonts.extraBold,
  },
  heroSubtitle: {
    color: 'rgba(255,255,255,0.88)',
  },
  body: {
    padding: theme.spacing.lg,
    gap: theme.spacing.lg,
    marginTop: -theme.spacing.md,
  },
  loadingBox: {
    alignItems: 'center',
    gap: theme.spacing.sm,
    paddingVertical: theme.spacing.xl,
    marginHorizontal: theme.spacing.lg,
    borderRadius: theme.radii.lg,
  },
  warningBox: {
    gap: theme.spacing.sm,
    borderRadius: theme.radii.lg,
    padding: theme.spacing.sm,
    borderWidth: 1,
    marginHorizontal: theme.spacing.lg,
  },
  errorBox: {
    gap: theme.spacing.sm,
    paddingHorizontal: theme.spacing.lg,
  },
  quickActions: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  sectionHeader: {
    gap: theme.spacing.xxs,
  },
  list: {
    gap: theme.spacing.md,
  },
});
