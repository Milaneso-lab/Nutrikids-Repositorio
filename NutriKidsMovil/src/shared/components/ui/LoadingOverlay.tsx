import React from 'react';
import { ActivityIndicator, Modal, StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';

import { AppText } from './Text';

interface LoadingOverlayProps {
  visible: boolean;
  message?: string;
}

export function LoadingOverlay({ visible, message }: LoadingOverlayProps): React.JSX.Element {
  return (
    <Modal transparent visible={visible} animationType="fade" accessibilityViewIsModal>
      <View style={styles.backdrop}>
        <View style={styles.card} accessibilityRole="progressbar">
          <ActivityIndicator size="large" color={theme.colors.primary[500]} />
          {message ? (
            <AppText variant="bodySmall" style={styles.message}>
              {message}
            </AppText>
          ) : null}
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  backdrop: {
    flex: 1,
    backgroundColor: theme.semantic.overlay,
    alignItems: 'center',
    justifyContent: 'center',
  },
  card: {
    backgroundColor: theme.semantic.surface,
    borderRadius: theme.radii.lg,
    padding: theme.spacing.lg,
    alignItems: 'center',
    gap: theme.spacing.sm,
    minWidth: 160,
    ...theme.shadows.md,
  },
  message: {
    textAlign: 'center',
  },
});
