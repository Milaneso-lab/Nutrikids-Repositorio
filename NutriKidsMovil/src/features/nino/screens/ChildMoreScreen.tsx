import React, { useMemo } from 'react';
import { Dimensions, Pressable, ScrollView, Text, View } from 'react-native';
import { CompositeScreenProps } from '@react-navigation/native';
import { BottomTabScreenProps } from '@react-navigation/bottom-tabs';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import Animated, { FadeInDown } from 'react-native-reanimated';

import type { ChildStackParamList, ChildTabParamList } from '@navigation/types';

import { KidScreenBackground } from '../components/KidScreenBackground';
import { useThemedKidStyles } from '../hooks/useThemedKidStyles';
import { useKidTheme } from '../providers/KidThemeProvider';
import { useChildSessionStore } from '../store/childSessionStore';
import type { ComingSoonFeature } from '../types/nino.types';

type Props = CompositeScreenProps<
  BottomTabScreenProps<ChildTabParamList, 'Mas'>,
  NativeStackScreenProps<ChildStackParamList>
>;

interface MenuItem {
  emoji: string;
  label: string;
  feature?: ComingSoonFeature;
  stackRoute?: 'AvatarEditor' | 'HabitsHome' | 'NotificationCenter' | 'ChildMessages';
  color: string;
}

const NUM_COLUMNS = 2;
const screenWidth = Dimensions.get('window').width;

export function ChildMoreScreen({ navigation }: Props): React.JSX.Element {
  const { colors, gradients } = useKidTheme();
  const activeChild = useChildSessionStore((s) => s.activeChild);

  const menuItems = useMemo<MenuItem[]>(
    () => [
      { emoji: '✅', label: 'Mis Hábitos', stackRoute: 'HabitsHome', color: colors.mint },
      { emoji: '📬', label: 'Notificaciones', stackRoute: 'NotificationCenter', color: colors.lavender },
      { emoji: '💌', label: 'Mensajes', stackRoute: 'ChildMessages', color: colors.bubblegum },
      { emoji: '🍎', label: 'Mi Alimentación', feature: 'alimentacion', color: colors.peach },
      { emoji: '🎨', label: 'Mi Avatar', stackRoute: 'AvatarEditor', color: colors.lavender },
      { emoji: '🛍️', label: 'Tienda', feature: 'tienda', color: colors.bubblegum },
      { emoji: '⚙️', label: 'Configuración', feature: 'configuracion', color: colors.sky },
    ],
    [colors],
  );

  const styles = useThemedKidStyles((t) => {
    const screenPadding = t.spacing.lg;
    const gridGap = t.spacing.sm;
    const tileWidth = (screenWidth - screenPadding * 2 - gridGap * (NUM_COLUMNS - 1)) / NUM_COLUMNS;

    return {
      content: {
        padding: screenPadding,
        paddingBottom: t.spacing['3xl'],
        gap: t.spacing.md,
      },
      title: {
        fontFamily: t.fonts.extraBold,
        fontSize: 28,
        color: t.colors.textOnGradient,
      },
      subtitle: {
        fontFamily: t.fonts.medium,
        fontSize: 15,
        color: t.colors.textOnGradientMuted,
      },
      grid: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        gap: gridGap,
        marginTop: t.spacing.sm,
      },
      gridItem: {
        width: tileWidth,
      },
      tile: {
        width: '100%',
        minHeight: 118,
        borderRadius: t.radii.card,
        padding: t.spacing.md,
        justifyContent: 'center',
        alignItems: 'center',
        gap: t.spacing.sm,
        borderWidth: 3,
        borderColor: t.colors.surface,
        ...t.shadow.card,
      },
      pressed: {
        opacity: 0.9,
        transform: [{ scale: 0.97 }],
      },
      tileEmoji: {
        fontSize: 34,
      },
      tileLabel: {
        fontFamily: t.fonts.bold,
        fontSize: 14,
        color: t.colors.ink,
        textAlign: 'center',
      },
    };
  });

  return (
    <KidScreenBackground gradient={gradients.more}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={styles.title}>Explorar ✨</Text>
        <Text style={styles.subtitle}>Más aventuras para {activeChild?.nombre ?? 'ti'}</Text>

        <View style={styles.grid}>
          {menuItems.map((item, index) => (
            <Animated.View
              key={item.label}
              entering={FadeInDown.delay(index * 60).springify()}
              style={styles.gridItem}
            >
              <Pressable
                accessibilityRole="button"
                accessibilityLabel={item.label}
                onPress={() => {
                  if (item.stackRoute) {
                    navigation.navigate(item.stackRoute);
                    return;
                  }
                  if (item.feature) {
                    navigation.navigate('ComingSoon', { feature: item.feature });
                  }
                }}
                style={({ pressed }) => [styles.tile, { backgroundColor: item.color }, pressed && styles.pressed]}
              >
                <Text style={styles.tileEmoji}>{item.emoji}</Text>
                <Text style={styles.tileLabel}>{item.label}</Text>
              </Pressable>
            </Animated.View>
          ))}
        </View>
      </ScrollView>
    </KidScreenBackground>
  );
}
