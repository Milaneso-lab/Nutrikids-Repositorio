import React from 'react';
import { StyleSheet, Text } from 'react-native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';

import {
  ChildHomeScreen,
  ChildMoreScreen,
  ChildProfileScreen,
} from '@features/nino';
import { useKidTheme } from '@features/nino/providers/KidThemeProvider';

import { ChildRetosScreen } from '@features/retos';
import { ChildLogrosScreen } from '@features/nino/screens/ChildPlaceholderTabScreens';

import { ChildTabParamList } from './types';

const Tab = createBottomTabNavigator<ChildTabParamList>();

function TabIcon({ emoji, focused }: { emoji: string; focused: boolean }): React.JSX.Element {
  return (
    <Text style={[styles.tabEmoji, focused && styles.tabEmojiFocused]} accessibilityElementsHidden>
      {emoji}
    </Text>
  );
}

export function ChildTabNavigator(): React.JSX.Element {
  const { colors, theme } = useKidTheme();

  return (
    <Tab.Navigator
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: colors.grape,
        tabBarInactiveTintColor: colors.inkSoft,
        tabBarStyle: [
          styles.tabBar,
          {
            backgroundColor: colors.tabBar,
            shadowColor: colors.shadow,
          },
          theme.shadow.card,
        ],
        tabBarLabelStyle: [styles.tabLabel, { fontFamily: theme.fonts.bold }],
        tabBarHideOnKeyboard: true,
        animation: 'shift',
      }}
    >
      <Tab.Screen
        name="Inicio"
        component={ChildHomeScreen}
        options={{
          tabBarLabel: 'Inicio',
          tabBarAccessibilityLabel: 'Inicio',
          tabBarIcon: ({ focused }) => <TabIcon emoji="🏠" focused={focused} />,
        }}
      />
      <Tab.Screen
        name="Perfil"
        component={ChildProfileScreen}
        options={{
          tabBarLabel: 'Mi Perfil',
          tabBarAccessibilityLabel: 'Mi perfil',
          tabBarIcon: ({ focused }) => <TabIcon emoji="⭐" focused={focused} />,
        }}
      />
      <Tab.Screen
        name="Retos"
        component={ChildRetosScreen}
        options={{
          tabBarLabel: 'Retos',
          tabBarAccessibilityLabel: 'Mis retos',
          tabBarIcon: ({ focused }) => <TabIcon emoji="🎯" focused={focused} />,
        }}
      />
      <Tab.Screen
        name="Logros"
        component={ChildLogrosScreen}
        options={{
          tabBarLabel: 'Logros',
          tabBarAccessibilityLabel: 'Mis logros',
          tabBarIcon: ({ focused }) => <TabIcon emoji="🏆" focused={focused} />,
        }}
      />
      <Tab.Screen
        name="Mas"
        component={ChildMoreScreen}
        options={{
          tabBarLabel: 'Más',
          tabBarAccessibilityLabel: 'Más opciones',
          tabBarIcon: ({ focused }) => <TabIcon emoji="✨" focused={focused} />,
        }}
      />
    </Tab.Navigator>
  );
}

const styles = StyleSheet.create({
  tabBar: {
    height: 68,
    paddingTop: 6,
    paddingBottom: 8,
    borderTopWidth: 0,
  },
  tabLabel: {
    fontSize: 11,
  },
  tabEmoji: {
    fontSize: 22,
    opacity: 0.65,
  },
  tabEmojiFocused: {
    fontSize: 26,
    opacity: 1,
  },
});
