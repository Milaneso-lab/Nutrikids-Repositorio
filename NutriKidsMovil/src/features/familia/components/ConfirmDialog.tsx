import React from 'react';
import { Modal, Pressable, StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';
import { Button } from '@shared/components/ui/Button';

import { ParentText } from './ParentText';
import { useParentTheme } from '../providers/ParentThemeProvider';

interface ConfirmDialogProps {
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

export function ConfirmDialog({
  visible,
  title,
  message,
  confirmLabel = 'Confirmar',
  cancelLabel = 'Cancelar',
  destructive = false,
  loading = false,
  onConfirm,
  onCancel,
}: ConfirmDialogProps): React.JSX.Element {
  const { colors } = useParentTheme();

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
          <ParentText variant="h3">{title}</ParentText>
          <ParentText variant="bodySmall" tone="secondary" style={styles.message}>
            {message}
          </ParentText>
          <View style={styles.actions}>
            <Button label={cancelLabel} variant="ghost" onPress={onCancel} disabled={loading} style={styles.action} />
            <Button
              label={confirmLabel}
              variant={destructive ? 'secondary' : 'primary'}
              loading={loading}
              onPress={onConfirm}
              style={[styles.action, destructive && styles.destructiveAction]}
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
    borderRadius: theme.radii.xl,
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
    flexDirection: 'row',
    justifyContent: 'flex-end',
    gap: theme.spacing.sm,
  },
  action: {
    minWidth: 100,
  },
  destructiveAction: {
    borderColor: theme.colors.error,
  },
});
