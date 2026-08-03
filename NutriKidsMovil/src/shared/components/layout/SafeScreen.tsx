import React from 'react';
import { StyleSheet } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { theme } from '@core/theme';

interface SafeScreenProps {
  children: React.ReactNode;
  edges?: ('top' | 'right' | 'bottom' | 'left')[];
  backgroundColor?: string;
}

export function SafeScreen({ children, edges = ['top', 'bottom'], backgroundColor }: SafeScreenProps): React.JSX.Element {
  return (
    <SafeAreaView style={[styles.container, backgroundColor ? { backgroundColor } : null]} edges={edges}>
      {children}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.semantic.background,
  },
});
