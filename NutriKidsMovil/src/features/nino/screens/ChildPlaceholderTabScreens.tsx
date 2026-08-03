import React from 'react';
import { StyleSheet, View } from 'react-native';

import { KidComingSoonView } from '../components/KidComingSoonView';
import { KidScreenBackground } from '../components/KidScreenBackground';
import { useKidTheme } from '../providers/KidThemeProvider';
import { useChildSessionStore } from '../store/childSessionStore';

export function ChildRetosScreen(): React.JSX.Element {
  const { gradients } = useKidTheme();
  const companion = useChildSessionStore((s) => s.activeChild?.companion);

  return (
    <KidScreenBackground gradient={gradients.comingSoon}>
      <View style={styles.flex}>
        <KidComingSoonView feature="retos" companion={companion} />
      </View>
    </KidScreenBackground>
  );
}

export function ChildLogrosScreen(): React.JSX.Element {
  const { gradients } = useKidTheme();
  const companion = useChildSessionStore((s) => s.activeChild?.companion);

  return (
    <KidScreenBackground gradient={gradients.comingSoon}>
      <View style={styles.flex}>
        <KidComingSoonView feature="logros" companion={companion} />
      </View>
    </KidScreenBackground>
  );
}

const styles = StyleSheet.create({
  flex: {
    flex: 1,
  },
});
