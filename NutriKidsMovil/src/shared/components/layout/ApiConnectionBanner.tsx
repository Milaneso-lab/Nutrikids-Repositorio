import React from 'react';
import { Platform, StyleSheet, View } from 'react-native';

import { env } from '@core/config/env';
import { theme } from '@core/theme';
import { AppText } from '@shared/components/ui/Text';

/**
 * En desarrollo, confirma que la app apunta a la API real (PostgreSQL vía FastAPI)
 * y no al modo demo con datos locales.
 */
export function ApiConnectionBanner(): React.JSX.Element | null {
  if (!__DEV__) {
    return null;
  }

  return (
    <View style={styles.banner} accessibilityRole="text" accessibilityLabel="Conectado a la API real">
      <AppText variant="caption" style={styles.text}>
        API real — {env.apiBaseUrl}
        {env.apiPrefix}
        {env.usesWebDevProxy ? ' (proxy web → localhost:8000)' : Platform.OS === 'web' ? ' (web → localhost)' : ''}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  banner: {
    backgroundColor: theme.colors.primary[700],
    paddingVertical: theme.spacing.xxs,
    paddingHorizontal: theme.spacing.sm,
    alignItems: 'center',
  },
  text: {
    color: theme.semantic.textInverse,
    letterSpacing: 0.3,
  },
});
