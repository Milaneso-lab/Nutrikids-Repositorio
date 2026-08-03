import React from 'react';
import { StyleSheet, View, ViewStyle } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';

import { theme } from '@core/theme';
import { useAuthTheme } from '@features/auth/providers/AuthThemeProvider';
import { SafeScreen } from '@shared/components/layout/SafeScreen';
import { KeyboardAwareScroll } from '@shared/components/layout/KeyboardAwareScroll';

interface AuthLayoutProps {
  children: React.ReactNode;
  footer?: React.ReactNode;
  scrollable?: boolean;
  contentStyle?: ViewStyle;
}

export function AuthLayout({
  children,
  footer,
  scrollable = true,
  contentStyle,
}: AuthLayoutProps): React.JSX.Element {
  const { colors } = useAuthTheme();

  const body = (
    <View style={[styles.content, contentStyle]}>
      {children}
      {footer ? <View style={styles.footer}>{footer}</View> : null}
    </View>
  );

  return (
    <SafeScreen edges={['top', 'bottom']} backgroundColor={colors.background}>
      <LinearGradient colors={[...colors.backgroundGradient]} style={styles.gradient}>
        {scrollable ? (
          <KeyboardAwareScroll contentContainerStyle={styles.scrollContent}>{body}</KeyboardAwareScroll>
        ) : (
          body
        )}
      </LinearGradient>
    </SafeScreen>
  );
}

const styles = StyleSheet.create({
  gradient: { flex: 1 },
  scrollContent: {
    flexGrow: 1,
  },
  content: {
    flexGrow: 1,
    paddingHorizontal: theme.spacing.lg,
    paddingTop: theme.spacing.xl,
    paddingBottom: theme.spacing.md,
    gap: theme.spacing.md,
  },
  footer: {
    marginTop: 'auto',
    paddingTop: theme.spacing.md,
  },
});
