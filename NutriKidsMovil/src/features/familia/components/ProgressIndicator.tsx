import React from 'react';
import { StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';

import { ParentText } from './ParentText';
import { useParentTheme } from '../providers/ParentThemeProvider';

interface ProgressIndicatorProps {
  progress: number;
  label?: string;
}

export function ProgressIndicator({ progress, label }: ProgressIndicatorProps): React.JSX.Element {
  const { colors } = useParentTheme();
  const clamped = Math.max(0, Math.min(progress, 1));
  const percentage = Math.round(clamped * 100);

  return (
    <View
      style={styles.wrapper}
      accessibilityRole="progressbar"
      accessibilityValue={{ min: 0, max: 100, now: percentage }}
    >
      {label ? (
        <ParentText variant="caption" tone="secondary">
          {label}
        </ParentText>
      ) : null}
      <View style={[styles.track, { backgroundColor: colors.progressTrack }]}>
        <View style={[styles.fill, { width: `${percentage}%`, backgroundColor: colors.accent }]} />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    gap: theme.spacing.xxs,
    marginTop: theme.spacing.xxs,
    alignSelf: 'stretch',
  },
  track: {
    height: 8,
    borderRadius: theme.radii.pill,
    overflow: 'hidden',
  },
  fill: {
    height: '100%',
    borderRadius: theme.radii.pill,
  },
});
