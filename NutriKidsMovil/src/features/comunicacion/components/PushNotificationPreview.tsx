import React from 'react';
import { StyleSheet, Text, View } from 'react-native';

import { theme } from '@core/theme';
import { useParentTheme } from '@features/familia/providers/ParentThemeProvider';

interface PushNotificationPreviewProps {
  title: string;
  body: string;
  emoji?: string;
}

export function PushNotificationPreview({ title, body, emoji = '🔔' }: PushNotificationPreviewProps): React.JSX.Element {
  const { colors } = useParentTheme();

  return (
    <View style={styles.wrapper} accessibilityLabel={`Vista previa: ${title}`}>
      <Text style={[styles.label, { color: colors.textSecondary }]}>Vista previa de notificación</Text>
      <View style={[styles.preview, { backgroundColor: colors.surfaceMuted, borderColor: colors.border }]}>
        <View style={[styles.iconBox, { backgroundColor: colors.accentSoft }]}>
          <Text style={styles.icon}>{emoji}</Text>
        </View>
        <View style={styles.textBox}>
          <Text style={[styles.appName, { color: colors.textSecondary }]}>NutriKids</Text>
          <Text style={[styles.title, { color: colors.textPrimary }]} numberOfLines={1}>
            {title}
          </Text>
          <Text style={[styles.body, { color: colors.textSecondary }]} numberOfLines={2}>
            {body}
          </Text>
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: { gap: theme.spacing.xs },
  label: { fontFamily: theme.fonts.medium, fontSize: 12 },
  preview: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
    borderRadius: 14,
    padding: theme.spacing.sm,
    borderWidth: 1,
  },
  iconBox: {
    width: 40,
    height: 40,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
  },
  icon: { fontSize: 20 },
  textBox: { flex: 1, gap: 1 },
  appName: { fontFamily: theme.fonts.semiBold, fontSize: 10 },
  title: { fontFamily: theme.fonts.bold, fontSize: 13 },
  body: { fontFamily: theme.fonts.regular, fontSize: 12 },
});
