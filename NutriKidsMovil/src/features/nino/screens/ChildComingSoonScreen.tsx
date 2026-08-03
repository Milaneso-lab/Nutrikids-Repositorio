import React from 'react';
import { StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import type { ChildStackParamList } from '@navigation/types';

import { KidComingSoonView } from '../components/KidComingSoonView';
import { KidScreenBackground } from '../components/KidScreenBackground';
import { useKidTheme } from '../providers/KidThemeProvider';
import { useChildSessionStore } from '../store/childSessionStore';

type Props = NativeStackScreenProps<ChildStackParamList, 'ComingSoon'>;

export function ChildComingSoonScreen({ route }: Props): React.JSX.Element {
  const { gradients } = useKidTheme();
  const activeChild = useChildSessionStore((s) => s.activeChild);

  return (
    <KidScreenBackground gradient={gradients.comingSoon}>
      <View style={styles.container}>
        <KidComingSoonView feature={route.params.feature} companion={activeChild?.companion} />
      </View>
    </KidScreenBackground>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
});
