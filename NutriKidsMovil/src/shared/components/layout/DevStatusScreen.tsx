import React from 'react';
import { ScrollView, StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';
import { Card } from '@shared/components/ui/Card';
import { AppText } from '@shared/components/ui/Text';
import { SafeScreen } from '@shared/components/layout/SafeScreen';

export interface DevStatusScreenProps {
  title: string;
  moduleLabel: string;
  description: string;
  plannedFeatures?: string[];
}

export function DevStatusScreen({
  title,
  moduleLabel,
  description,
  plannedFeatures = [],
}: DevStatusScreenProps): React.JSX.Element {
  return (
    <SafeScreen>
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.badge}>
          <AppText variant="caption" style={styles.badgeText}>
            {moduleLabel.toUpperCase()}
          </AppText>
        </View>

        <AppText variant="h2">{title}</AppText>
        <AppText variant="body" color="secondary">
          {description}
        </AppText>

        <Card elevated={false}>
          <AppText variant="label" style={styles.cardTitle}>
            Estado de desarrollo
          </AppText>
          <AppText variant="bodySmall" color="secondary">
            Esta sección es navegable para la demo visual. La funcionalidad final llegará en la siguiente épica.
          </AppText>
        </Card>

        {plannedFeatures.length > 0 ? (
          <Card elevated={false}>
            <AppText variant="label" style={styles.cardTitle}>
              Próximamente
            </AppText>
            {plannedFeatures.map((feature) => (
              <AppText key={feature} variant="bodySmall" color="secondary" style={styles.featureItem}>
                • {feature}
              </AppText>
            ))}
          </Card>
        ) : null}
      </ScrollView>
    </SafeScreen>
  );
}

const styles = StyleSheet.create({
  content: {
    padding: theme.spacing.lg,
    gap: theme.spacing.md,
    paddingBottom: theme.spacing['3xl'],
  },
  badge: {
    alignSelf: 'flex-start',
    backgroundColor: theme.colors.primary[100],
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: theme.spacing.xxs,
    borderRadius: theme.radii.pill,
  },
  badgeText: {
    color: theme.colors.primary[800],
  },
  cardTitle: {
    marginBottom: theme.spacing.xs,
    color: theme.colors.primary[700],
  },
  featureItem: {
    marginTop: theme.spacing.xxs,
  },
});
