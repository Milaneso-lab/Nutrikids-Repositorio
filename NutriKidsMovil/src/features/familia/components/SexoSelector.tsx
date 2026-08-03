import React from 'react';
import { Pressable, StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';
import { AppText } from '@shared/components/ui/Text';

import { useParentTheme } from '../providers/ParentThemeProvider';
import type { SexoNino } from '../types/familia.types';

const OPTIONS: Array<{ value: SexoNino; label: string }> = [
  { value: 'masculino', label: 'Masculino' },
  { value: 'femenino', label: 'Femenino' },
  { value: 'otro', label: 'Otro' },
];

interface SexoSelectorProps {
  value: SexoNino | '';
  onChange: (sexo: SexoNino) => void;
  error?: string;
}

export function SexoSelector({ value, onChange, error }: SexoSelectorProps): React.JSX.Element {
  const { colors } = useParentTheme();

  return (
    <View style={styles.wrapper}>
      <AppText variant="label" style={{ color: colors.textSecondary }}>
        Sexo
      </AppText>
      <View style={styles.row}>
        {OPTIONS.map((option) => {
          const selected = value === option.value;
          return (
            <Pressable
              key={option.value}
              accessibilityRole="radio"
              accessibilityState={{ selected }}
              accessibilityLabel={option.label}
              onPress={() => onChange(option.value)}
              style={[
                styles.chip,
                {
                  borderColor: selected ? colors.accent : colors.border,
                  backgroundColor: selected ? colors.accentSoft : colors.inputBackground,
                },
              ]}
            >
              <AppText
                variant="caption"
                style={selected ? { color: colors.accent, fontFamily: theme.fonts.semiBold } : { color: colors.textPrimary }}
              >
                {option.label}
              </AppText>
            </Pressable>
          );
        })}
      </View>
      {error ? (
        <AppText variant="caption" color="error" accessibilityRole="alert">
          {error}
        </AppText>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    gap: theme.spacing.xxs,
  },
  row: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.sm,
  },
  chip: {
    minHeight: 44,
    paddingHorizontal: theme.spacing.md,
    borderRadius: theme.radii.pill,
    borderWidth: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
});
