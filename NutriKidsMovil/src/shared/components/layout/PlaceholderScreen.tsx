import React from 'react';
import { StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';
import { Button } from '@shared/components/ui/Button';
import { AppText } from '@shared/components/ui/Text';
import { SafeScreen } from '@shared/components/layout/SafeScreen';

interface PlaceholderScreenProps {
  title: string;
  description?: string;
  moduleLabel?: string;
  onNavigateNext?: () => void;
  nextLabel?: string;
}

/**
 * Pantalla genérica de scaffolding.
 * No contiene lógica de negocio ni UI final de producto.
 */
export function PlaceholderScreen({
  title,
  description = 'Pantalla placeholder — infraestructura de navegación.',
  moduleLabel,
  onNavigateNext,
  nextLabel = 'Continuar',
}: PlaceholderScreenProps): React.JSX.Element {
  return (
    <SafeScreen>
      <View style={styles.content} accessibilityLabel={`Placeholder ${title}`}>
        {moduleLabel ? (
          <AppText variant="caption" color="secondary" style={styles.badge}>
            {moduleLabel}
          </AppText>
        ) : null}
        <AppText variant="h2">{title}</AppText>
        <AppText variant="body" color="secondary" style={styles.description}>
          {description}
        </AppText>
        {onNavigateNext ? <Button label={nextLabel} onPress={onNavigateNext} fullWidth /> : null}
      </View>
    </SafeScreen>
  );
}

const styles = StyleSheet.create({
  content: {
    flex: 1,
    padding: theme.spacing.lg,
    justifyContent: 'center',
    gap: theme.spacing.md,
  },
  badge: {
    alignSelf: 'flex-start',
    backgroundColor: theme.semantic.surfaceMuted,
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: theme.spacing.xxs,
    borderRadius: theme.radii.pill,
    overflow: 'hidden',
  },
  description: {
    marginBottom: theme.spacing.sm,
  },
});
