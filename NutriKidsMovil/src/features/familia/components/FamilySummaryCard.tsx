import React from 'react';
import { StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';
import { AppText } from '@shared/components/ui/Text';
import { formatPoints } from '@shared/utils/format';

import { useParentTheme } from '../providers/ParentThemeProvider';
import type { FamilySummary } from '../types/familia.types';

interface FamilySummaryCardProps {
  summary: FamilySummary;
}

function formatLastActivity(iso: string | null): string {
  if (!iso) {
    return 'Sin actividad reciente';
  }
  try {
    return new Intl.DateTimeFormat('es-MX', {
      dateStyle: 'medium',
      timeStyle: 'short',
    }).format(new Date(iso));
  } catch {
    return 'Actividad reciente';
  }
}

export function FamilySummaryCard({ summary }: FamilySummaryCardProps): React.JSX.Element {
  const { colors } = useParentTheme();

  return (
    <View
      style={[
        styles.card,
        {
          backgroundColor: colors.surface,
          borderColor: colors.border,
          shadowColor: colors.shadow,
        },
      ]}
    >
      <AppText variant="label" style={{ color: colors.textSecondary }}>
        Resumen familiar
      </AppText>
      <View style={styles.statsRow}>
        <StatItem label="Hijos" value={String(summary.totalHijos)} tint={colors.statPill} text={colors.textPrimary} subtext={colors.textSecondary} />
        <StatItem label="Nivel prom." value={String(summary.nivelPromedio)} tint={colors.accentSoft} text={colors.accent} subtext={colors.textSecondary} />
        <StatItem label="Puntos" value={formatPoints(summary.puntosTotales)} tint={colors.surfaceMuted} text={colors.textPrimary} subtext={colors.textSecondary} />
      </View>
      <AppText variant="caption" style={{ color: colors.textSecondary }}>
        Última actividad: {formatLastActivity(summary.ultimaActividad)}
      </AppText>
    </View>
  );
}

function StatItem({
  label,
  value,
  tint,
  text,
  subtext,
}: {
  label: string;
  value: string;
  tint: string;
  text: string;
  subtext: string;
}): React.JSX.Element {
  return (
    <View style={[styles.stat, { backgroundColor: tint }]} accessibilityLabel={`${label}: ${value}`}>
      <AppText variant="h2" style={{ color: text }}>
        {value}
      </AppText>
      <AppText variant="caption" style={{ color: subtext }}>
        {label}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    gap: theme.spacing.sm,
    borderRadius: theme.radii.xl,
    padding: theme.spacing.md,
    borderWidth: 1,
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.06,
    shadowRadius: 18,
    elevation: 2,
  },
  statsRow: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  stat: {
    flex: 1,
    alignItems: 'center',
    gap: theme.spacing.xxs,
    borderRadius: theme.radii.lg,
    paddingVertical: theme.spacing.sm,
    paddingHorizontal: theme.spacing.xs,
  },
});
