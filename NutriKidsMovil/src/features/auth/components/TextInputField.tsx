import React, { useRef, useState } from 'react';
import { Pressable, StyleSheet, TextInput, TextInputProps, View, type NativeSyntheticEvent, type TextInputFocusEventData } from 'react-native';

import { theme } from '@core/theme';
import { useFormThemeColors } from '@shared/theme/useFormThemeColors';
import { useKeyboardScroll } from '@shared/components/layout/KeyboardAwareScroll';
import { AppText } from '@shared/components/ui/Text';

interface TextInputFieldProps extends TextInputProps {
  label: string;
  error?: string;
}

export function TextInputField({ label, error, style, onFocus, ...rest }: TextInputFieldProps): React.JSX.Element {
  const colors = useFormThemeColors();
  const inputRef = useRef<TextInput>(null);
  const keyboardScroll = useKeyboardScroll();

  function handleFocus(event: NativeSyntheticEvent<TextInputFocusEventData>): void {
    onFocus?.(event);
    if (inputRef.current) {
      keyboardScroll?.scrollToFocused(inputRef.current);
    }
  }

  return (
    <View style={styles.wrapper}>
      <AppText variant="label" style={[styles.label, { color: colors.textSecondary }]}>
        {label}
      </AppText>
      <TextInput
        ref={inputRef}
        accessibilityLabel={label}
        placeholderTextColor={colors.inputPlaceholder}
        style={[
          styles.input,
          {
            backgroundColor: colors.inputBackground,
            borderColor: error ? theme.colors.error : colors.inputBorder,
            color: colors.inputText,
          },
          style,
        ]}
        onFocus={handleFocus}
        {...rest}
      />
      {error ? (
        <AppText variant="caption" color="error" accessibilityRole="alert">
          {error}
        </AppText>
      ) : null}
    </View>
  );
}

interface PasswordInputFieldProps extends Omit<TextInputProps, 'secureTextEntry'> {
  label: string;
  error?: string;
}

function sanitizePinDigits(value: string, maxLength: number): string {
  return value.replace(/\D/g, '').slice(0, maxLength);
}

export function PasswordInputField({ label, error, style, onFocus, ...rest }: PasswordInputFieldProps): React.JSX.Element {
  const colors = useFormThemeColors();
  const [visible, setVisible] = useState(false);
  const inputRef = useRef<TextInput>(null);
  const keyboardScroll = useKeyboardScroll();

  function handleFocus(event: NativeSyntheticEvent<TextInputFocusEventData>): void {
    onFocus?.(event);
    if (inputRef.current) {
      keyboardScroll?.scrollToFocused(inputRef.current);
    }
  }

  return (
    <View style={styles.wrapper}>
      <AppText variant="label" style={[styles.label, { color: colors.textSecondary }]}>
        {label}
      </AppText>
      <View style={styles.passwordRow}>
        <TextInput
          ref={inputRef}
          accessibilityLabel={label}
          placeholderTextColor={colors.inputPlaceholder}
          secureTextEntry={!visible}
          autoCapitalize="none"
          autoCorrect={false}
          textContentType="password"
          style={[
            styles.input,
            styles.passwordInput,
            {
              backgroundColor: colors.inputBackground,
              borderColor: error ? theme.colors.error : colors.inputBorder,
              color: colors.inputText,
            },
            style,
          ]}
          onFocus={handleFocus}
          {...rest}
        />
        <Pressable
          accessibilityRole="button"
          accessibilityLabel={visible ? 'Ocultar contraseña' : 'Mostrar contraseña'}
          onPress={() => setVisible((v) => !v)}
          style={styles.toggle}
          hitSlop={8}
        >
          <AppText variant="caption" style={[styles.toggleText, { color: colors.accent }]}>
            {visible ? 'Ocultar' : 'Ver'}
          </AppText>
        </Pressable>
      </View>
      {error ? (
        <AppText variant="caption" color="error" accessibilityRole="alert">
          {error}
        </AppText>
      ) : null}
    </View>
  );
}

interface PinInputFieldProps extends Omit<PasswordInputFieldProps, 'onChangeText' | 'keyboardType' | 'maxLength'> {
  onChangeText: (value: string) => void;
  maxLength?: number;
}

/** Campo PIN: solo dígitos 0-9, sin letras ni símbolos. */
export function PinInputField({
  onChangeText,
  maxLength = 6,
  ...rest
}: PinInputFieldProps): React.JSX.Element {
  return (
    <PasswordInputField
      keyboardType="number-pad"
      inputMode="numeric"
      maxLength={maxLength}
      autoComplete="off"
      textContentType="oneTimeCode"
      onChangeText={(text) => onChangeText(sanitizePinDigits(text, maxLength))}
      {...rest}
    />
  );
}

const styles = StyleSheet.create({
  wrapper: {
    gap: theme.spacing.xxs,
  },
  label: {},
  input: {
    minHeight: 48,
    borderWidth: 1.5,
    borderRadius: theme.radii.md,
    paddingHorizontal: theme.spacing.md,
    fontFamily: theme.fonts.regular,
    fontSize: theme.typography.body.fontSize,
  },
  passwordRow: {
    position: 'relative',
  },
  passwordInput: {
    paddingRight: theme.spacing['3xl'],
  },
  toggle: {
    position: 'absolute',
    right: theme.spacing.sm,
    top: 0,
    bottom: 0,
    minWidth: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  toggleText: {
    fontFamily: theme.fonts.semiBold,
  },
});
