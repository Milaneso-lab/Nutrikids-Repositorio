import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { StatusBar } from 'expo-status-bar';

import { theme } from '@core/theme';
import { AvatarEditorScreen, ChildComingSoonScreen, ChildProfileEditScreen } from '@features/nino';
import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import { GamePlayScreen } from '@features/retos';
import { HabitCalendarScreen, HabitsHomeScreen, HabitStatisticsScreen } from '@features/habitos';
import {
  ChildMessagesScreen,
  NotificationCenterScreen,
  RemindersSettingsScreen,
} from '@features/comunicacion';

import { ChildTabNavigator } from './ChildTabNavigator';
import { ChildStackParamList } from './types';

const Stack = createNativeStackNavigator<ChildStackParamList>();

export function ChildNavigator(): React.JSX.Element {
  const { colors, isDark } = useKidTheme();

  return (
    <>
      <StatusBar style={isDark ? 'light' : 'dark'} />
      <Stack.Navigator
        screenOptions={{
          headerShown: true,
          headerStyle: { backgroundColor: colors.headerBackground },
          headerTitleStyle: {
            fontFamily: theme.fonts.bold,
            fontSize: 18,
            color: colors.headerText,
          },
          headerTintColor: colors.headerTint,
          headerShadowVisible: false,
          contentStyle: { backgroundColor: colors.surfaceMuted },
          animation: 'slide_from_right',
        }}
      >
        <Stack.Screen name="ChildTabs" component={ChildTabNavigator} options={{ headerShown: false }} />
        <Stack.Screen name="ChildProfileEdit" component={ChildProfileEditScreen} options={{ title: 'Editar perfil' }} />
        <Stack.Screen name="AvatarEditor" component={AvatarEditorScreen} options={{ title: 'Mi avatar' }} />
        <Stack.Screen name="HabitsHome" component={HabitsHomeScreen} options={{ title: 'Mis Hábitos' }} />
        <Stack.Screen name="HabitCalendar" component={HabitCalendarScreen} options={{ title: 'Calendario' }} />
        <Stack.Screen name="HabitStatistics" component={HabitStatisticsScreen} options={{ title: 'Estadísticas' }} />
        <Stack.Screen name="NotificationCenter" component={NotificationCenterScreen} options={{ title: 'Notificaciones' }} />
        <Stack.Screen name="ChildMessages" component={ChildMessagesScreen} options={{ title: 'Mensajes' }} />
        <Stack.Screen name="RemindersSettings" component={RemindersSettingsScreen} options={{ title: 'Recordatorios' }} />
        <Stack.Screen
          name="ComingSoon"
          component={ChildComingSoonScreen}
          options={({ route }) => ({ title: route.params.feature })}
        />
        <Stack.Screen
          name="GamePlay"
          component={GamePlayScreen}
          options={{ title: 'Jugar reto' }}
        />
      </Stack.Navigator>
    </>
  );
}
