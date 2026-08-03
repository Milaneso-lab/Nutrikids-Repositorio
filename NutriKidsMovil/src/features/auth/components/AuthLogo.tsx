import React from 'react';
import { Image, StyleSheet, View } from 'react-native';

import { APP_NAME } from '@core/config/constants';
import { theme } from '@core/theme';
import { useAuthTheme } from '@features/auth/providers/AuthThemeProvider';
import { AppText } from '@shared/components/ui/Text';

interface AuthLogoProps {
  subtitle?: string;
}

export function AuthLogo({ subtitle }: AuthLogoProps): React.JSX.Element {
  const { colors } = useAuthTheme();

  return (
    <View style={styles.container} accessibilityRole="header">
      <Image
        source={require('../../../../assets/nutrikids-logo.png')}
        style={styles.logo}
        accessibilityLabel={`Logo ${APP_NAME}`}
        accessibilityIgnoresInvertColors
      />
      <AppText variant="h1" style={[styles.title, { color: colors.textPrimary }]}>
        {APP_NAME}
      </AppText>
      {subtitle ? (
        <AppText variant="body" style={[styles.subtitle, { color: colors.textSecondary }]}>
          {subtitle}
        </AppText>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    alignItems: 'center',
    gap: theme.spacing.sm,
    marginBottom: theme.spacing.md,
  },
  logo: {
    width: 140,
    height: 140,
    resizeMode: 'contain',
  },
  title: {
    textAlign: 'center',
  },
  subtitle: {
    textAlign: 'center',
    maxWidth: 320,
  },
});
