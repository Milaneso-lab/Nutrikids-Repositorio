import React from 'react';
import { StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';
import { AppText } from '@shared/components/ui/Text';

interface FormErrorBannerProps {
  message: string;
}

export function FormErrorBanner({ message }: FormErrorBannerProps): React.JSX.Element {
  return (
    <View style={styles.banner} accessibilityRole="alert">
      <AppText variant="bodySmall" color="error">
        {message}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  banner: {
    backgroundColor: '#FFEBEE',
    borderRadius: theme.radii.md,
    padding: theme.spacing.sm,
    borderWidth: 1,
    borderColor: '#FFCDD2',
  },
});
