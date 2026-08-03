import React from 'react';
import { Modal, Pressable, StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';

import { KidText } from './KidText';
import { KidActionButton } from './KidActionButton';
import { useKidTheme } from '../providers/KidThemeProvider';

interface KidConfirmDialogProps {
  visible: boolean;
  title: string;
  message: string;
  confirmLabel?: string;
  cancelLabel?: string;
  destructive?: boolean;
  loading?: boolean;
  onConfirm: () => void;
  onCancel: () => void;
}

export function KidConfirmDialog({
  visible,
  title,
  message,
  confirmLabel = 'Confirmar',
  cancelLabel = 'Cancelar',
  destructive = false,
  loading = false,
  onConfirm,
  onCancel,
}: KidConfirmDialogProps): React.JSX.Element {
  const { colors } = useKidTheme();

  return (
    <Modal visible={visible} transparent animationType="fade" onRequestClose={onCancel}>
      <Pressable style={styles.backdrop} onPress={onCancel} accessibilityRole="button">
        <Pressable
          style={[
            styles.dialog,
            {
              backgroundColor: colors.surfaceElevated,
              borderColor: colors.border,
              shadowColor: colors.shadow,
            },
          ]}
          onPress={(event) => event.stopPropagation()}
        >
          <KidText bold size={18}>
            {title}
          </KidText>
          <KidText tone="secondary" size={14} style={styles.message}>
            {message}
          </KidText>
          <View style={styles.actions}>
            <KidActionButton
              label={cancelLabel}
              variant="secondary"
              onPress={onCancel}
              disabled={loading}
              style={styles.action}
            />
            <KidActionButton
              label={confirmLabel}
              variant={destructive ? 'coral' : 'mint'}
              onPress={onConfirm}
              disabled={loading}
              style={styles.action}
            />
          </View>
        </Pressable>
      </Pressable>
    </Modal>
  );
}

const styles = StyleSheet.create({
  backdrop: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.55)',
    justifyContent: 'center',
    padding: theme.spacing.lg,
  },
  dialog: {
    borderRadius: 24,
    padding: theme.spacing.lg,
    gap: theme.spacing.md,
    borderWidth: 1,
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.2,
    shadowRadius: 20,
    elevation: 8,
  },
  message: {
    lineHeight: 22,
  },
  actions: {
    gap: theme.spacing.sm,
  },
  action: {
    alignSelf: 'stretch',
  },
});
