import React from 'react';
import { Animated, StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';

export function AuthFormSkeleton(): React.JSX.Element {
  const opacity = React.useRef(new Animated.Value(0.4)).current;

  React.useEffect(() => {
    const animation = Animated.loop(
      Animated.sequence([
        Animated.timing(opacity, { toValue: 1, duration: 700, useNativeDriver: true }),
        Animated.timing(opacity, { toValue: 0.4, duration: 700, useNativeDriver: true }),
      ]),
    );
    animation.start();
    return () => animation.stop();
  }, [opacity]);

  return (
    <View style={styles.container} accessibilityLabel="Cargando formulario">
      {[1, 2, 3].map((key) => (
        <Animated.View key={key} style={[styles.block, { opacity }]} />
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    gap: theme.spacing.md,
  },
  block: {
    height: 48,
    borderRadius: theme.radii.md,
    backgroundColor: theme.colors.neutral[200],
  },
});
