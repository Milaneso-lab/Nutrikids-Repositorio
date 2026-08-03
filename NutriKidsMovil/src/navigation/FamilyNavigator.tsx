import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { StatusBar } from 'expo-status-bar';

import { theme } from '@core/theme';
import {
  ChildFormScreen,
  ChildProfileScreen,
  FamilyDashboardScreen,
} from '@features/familia';
import { ParentProfileEditScreen } from '@features/familia/screens/ParentProfileEditScreen';
import { ParentHeaderMenu } from '@features/familia/components/ParentHeaderMenu';
import { useParentTheme } from '@features/familia/providers/ParentThemeProvider';
import { SendFamilyMessageScreen } from '@features/comunicacion';

import { FamilyStackParamList } from './types';

const Stack = createNativeStackNavigator<FamilyStackParamList>();

export function FamilyNavigator(): React.JSX.Element {
  const { colors, isDark } = useParentTheme();

  return (
    <>
      <StatusBar style={isDark ? 'light' : 'dark'} />
      <Stack.Navigator
        screenOptions={{
          headerStyle: { backgroundColor: colors.surface },
          headerTitleStyle: {
            ...theme.typography.h3,
            color: colors.textPrimary,
          },
          headerTintColor: colors.accent,
          headerShadowVisible: false,
          headerBackTitle: 'Atrás',
          headerRight: () => <ParentHeaderMenu />,
          contentStyle: { backgroundColor: colors.background },
          animation: 'slide_from_right',
        }}
      >
        <Stack.Screen
          name="FamilyDashboard"
          component={FamilyDashboardScreen}
          options={{ title: 'Mi familia' }}
        />
        <Stack.Screen
          name="ParentProfileEdit"
          component={ParentProfileEditScreen}
          options={{ title: 'Editar perfil' }}
        />
        <Stack.Screen
          name="ChildForm"
          component={ChildFormScreen}
          options={({ route }) => ({
            title: route.params?.ninoId ? 'Editar hijo' : 'Registrar hijo',
          })}
        />
        <Stack.Screen
          name="ChildProfile"
          component={ChildProfileScreen}
          options={{ title: 'Perfil del hijo' }}
        />
        <Stack.Screen
          name="SendFamilyMessage"
          component={SendFamilyMessageScreen}
          options={{ title: 'Enviar mensaje' }}
        />
      </Stack.Navigator>
    </>
  );
}
