import React from 'react';
import { StyleSheet, View, ViewProps } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';

import { useKidTheme } from '../providers/KidThemeProvider';

interface KidScreenBackgroundProps extends ViewProps {
  gradient?: readonly [string, string];
  children: React.ReactNode;
}

export function KidScreenBackground({
  gradient,
  children,
  style,
  ...rest
}: KidScreenBackgroundProps): React.JSX.Element {
  const { gradients, isDark } = useKidTheme();
  const colors = gradient ?? gradients.home;

  return (
    <LinearGradient colors={[...colors]} style={[styles.flex, style]} {...rest}>
      <View style={[styles.blobTop, isDark && styles.blobTopDark]} />
      <View style={[styles.blobBottom, isDark && styles.blobBottomDark]} />
      <View style={styles.content}>{children}</View>
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  flex: {
    flex: 1,
  },
  content: {
    flex: 1,
  },
  blobTop: {
    position: 'absolute',
    top: -40,
    right: -30,
    width: 140,
    height: 140,
    borderRadius: 70,
    backgroundColor: 'rgba(255,255,255,0.25)',
  },
  blobTopDark: {
    backgroundColor: 'rgba(255,255,255,0.08)',
  },
  blobBottom: {
    position: 'absolute',
    bottom: 80,
    left: -20,
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: 'rgba(255,255,255,0.18)',
  },
  blobBottomDark: {
    backgroundColor: 'rgba(255,255,255,0.06)',
  },
});
