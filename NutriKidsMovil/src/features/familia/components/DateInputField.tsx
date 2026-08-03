import React, { useEffect, useState } from 'react';
import { Modal, Platform, Pressable, StyleSheet, TextInput, View } from 'react-native';
import DateTimePicker, { DateTimePickerEvent } from '@react-native-community/datetimepicker';

import { theme } from '@core/theme';
import { useParentTheme } from '../providers/ParentThemeProvider';
import { ParentText } from './ParentText';

import {
  displayDateToIso,
  formatBirthDateInput,
  getBirthDateLimits,
  isoToDisplayDate,
} from '../utils/birthDate';

interface DateInputFieldProps {
  label: string;
  value: string;
  onChange: (isoDate: string) => void;
  error?: string;
}

function toIsoDate(date: Date): string {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function parseIsoDate(value: string): Date {
  const { maximumDate } = getBirthDateLimits();
  if (!value) {
    return maximumDate;
  }
  const parsed = new Date(`${value}T12:00:00`);
  return Number.isNaN(parsed.getTime()) ? maximumDate : parsed;
}

export function DateInputField({ label, value, onChange, error }: DateInputFieldProps): React.JSX.Element {
  const { colors } = useParentTheme();
  const { minimumDate, maximumDate } = getBirthDateLimits();
  const [display, setDisplay] = useState(() => isoToDisplayDate(value));
  const [focused, setFocused] = useState(false);
  const [showPicker, setShowPicker] = useState(false);
  const [draftDate, setDraftDate] = useState(() => parseIsoDate(value));

  useEffect(() => {
    if (!focused) {
      setDisplay(isoToDisplayDate(value));
    }
  }, [focused, value]);

  function handleTextChange(text: string): void {
    const formatted = formatBirthDateInput(text);
    setDisplay(formatted);
    const iso = displayDateToIso(formatted);
    onChange(iso ?? '');
  }

  function handleAndroidPickerChange(event: DateTimePickerEvent, selected?: Date): void {
    setShowPicker(false);
    if (event.type === 'dismissed' || !selected) {
      return;
    }
    const iso = toIsoDate(selected);
    onChange(iso);
    setDisplay(isoToDisplayDate(iso));
  }

  function confirmIosDate(): void {
    const iso = toIsoDate(draftDate);
    onChange(iso);
    setDisplay(isoToDisplayDate(iso));
    setShowPicker(false);
  }

  const showNativePicker = Platform.OS !== 'web';

  return (
    <View style={styles.wrapper}>
      <ParentText variant="label" tone="secondary">
        {label}
      </ParentText>
      <View style={styles.inputRow}>
        <TextInput
          accessibilityLabel={label}
          value={display}
          onChangeText={handleTextChange}
          onFocus={() => setFocused(true)}
          onBlur={() => setFocused(false)}
          placeholder="DD/MM/AAAA"
          placeholderTextColor={colors.inputPlaceholder}
          keyboardType="number-pad"
          maxLength={10}
          style={[
            styles.input,
            styles.textInput,
            {
              borderColor: error ? theme.colors.error : colors.inputBorder,
              backgroundColor: colors.inputBackground,
              color: colors.inputText,
            },
          ]}
        />
        {showNativePicker ? (
          <Pressable
            accessibilityRole="button"
            accessibilityLabel="Abrir calendario"
            onPress={() => {
              setDraftDate(parseIsoDate(value));
              setShowPicker(true);
            }}
            style={[
              styles.calendarButton,
              { borderColor: colors.inputBorder, backgroundColor: colors.inputBackground },
            ]}
            hitSlop={8}
          >
            <ParentText style={styles.calendarIcon}>📅</ParentText>
          </Pressable>
        ) : null}
      </View>
      <ParentText variant="caption" tone="secondary">
        Edad permitida: entre 2 y 20 años
      </ParentText>
      {error ? (
        <ParentText variant="caption" style={{ color: theme.colors.error }} accessibilityRole="alert">
          {error}
        </ParentText>
      ) : null}

      {Platform.OS === 'android' && showPicker ? (
        <DateTimePicker
          value={parseIsoDate(value)}
          mode="date"
          display="default"
          maximumDate={maximumDate}
          minimumDate={minimumDate}
          onChange={handleAndroidPickerChange}
        />
      ) : null}

      {Platform.OS === 'ios' ? (
        <Modal visible={showPicker} transparent animationType="slide" onRequestClose={() => setShowPicker(false)}>
          <Pressable style={styles.modalBackdrop} onPress={() => setShowPicker(false)} />
          <View style={[styles.iosSheet, { backgroundColor: colors.surfaceElevated }]}>
            <View style={[styles.iosToolbar, { borderBottomColor: colors.border }]}>
              <Pressable accessibilityRole="button" onPress={() => setShowPicker(false)} hitSlop={8}>
                <ParentText variant="body" style={{ color: colors.textSecondary }}>
                  Cancelar
                </ParentText>
              </Pressable>
              <ParentText variant="label">{label}</ParentText>
              <Pressable accessibilityRole="button" onPress={confirmIosDate} hitSlop={8}>
                <ParentText variant="body" style={{ color: colors.accent, fontFamily: theme.fonts.semiBold }}>
                  Listo
                </ParentText>
              </Pressable>
            </View>
            <DateTimePicker
              value={draftDate}
              mode="date"
              display="spinner"
              maximumDate={maximumDate}
              minimumDate={minimumDate}
              onChange={(_event, selected) => {
                if (selected) {
                  setDraftDate(selected);
                }
              }}
              style={styles.iosPicker}
            />
          </View>
        </Modal>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    gap: theme.spacing.xxs,
  },
  inputRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: theme.spacing.xs,
  },
  input: {
    minHeight: 48,
    borderWidth: 1,
    borderRadius: theme.radii.md,
  },
  textInput: {
    flex: 1,
    paddingHorizontal: theme.spacing.md,
    fontFamily: theme.fonts.regular,
    fontSize: theme.typography.body.fontSize,
  },
  calendarButton: {
    minHeight: 48,
    minWidth: 48,
    borderWidth: 1,
    borderRadius: theme.radii.md,
    alignItems: 'center',
    justifyContent: 'center',
  },
  calendarIcon: {
    fontSize: 20,
  },
  modalBackdrop: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.35)',
  },
  iosSheet: {
    borderTopLeftRadius: theme.radii.lg,
    borderTopRightRadius: theme.radii.lg,
    paddingBottom: theme.spacing.lg,
  },
  iosToolbar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: theme.spacing.md,
    paddingVertical: theme.spacing.sm,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  iosPicker: {
    height: 216,
  },
});
