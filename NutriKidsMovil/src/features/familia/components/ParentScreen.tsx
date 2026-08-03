import React from 'react';
import { StyleSheet } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { useParentTheme } from '@features/familia/providers/ParentThemeProvider';

interface ParentScreenProps {
  children: React.ReactNode;
  edges?: ('top' | 'right' | 'bottom' | 'left')[];
}

export function ParentScreen({ children, edges = ['bottom'] }: ParentScreenProps): React.JSX.Element {
  const { colors } = useParentTheme();

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: colors.background }]} edges={edges}>
      {children}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
});
