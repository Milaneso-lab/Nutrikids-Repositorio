import React from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { useNavigation } from '@react-navigation/native';

import { theme } from '@core/theme';
import { useAuth } from '@features/auth/hooks/useAuth';
import { DevStatusScreen } from '@shared/components/layout/DevStatusScreen';
import { AppText } from '@shared/components/ui/Text';
import { useAppStore } from '@state/stores/appStore';

import { MainTabParamList } from './types';

const Tab = createBottomTabNavigator<MainTabParamList>();

const TAB_SCREENS: Array<{
  name: keyof MainTabParamList;
  title: string;
  moduleLabel: string;
  description: string;
  plannedFeatures: string[];
}> = [
  {
    name: 'Home',
    title: 'Inicio',
    moduleLabel: 'home',
    description: 'Vista principal del niño: racha, puntos y hábito destacado del día.',
    plannedFeatures: ['Saludo personalizado', 'Racha de días', 'Resumen de puntos'],
  },
  {
    name: 'Habitos',
    title: 'Hábitos del día',
    moduleLabel: 'habitos',
    description: 'Checklist visual de hábitos asignados por el nutriólogo.',
    plannedFeatures: ['Marcar hábitos completados', 'Animación de confeti', 'Cola offline'],
  },
  {
    name: 'Retos',
    title: 'Retos',
    moduleLabel: 'retos',
    description: 'Tarjetas de retos activos con barra de progreso.',
    plannedFeatures: ['Retos activos', 'Historial', 'Notificaciones push'],
  },
  {
    name: 'Avatar',
    title: 'Avatar / Tienda',
    moduleLabel: 'avatar',
    description: 'Personalización del avatar con puntos ganados.',
    plannedFeatures: ['Editor de avatar', 'Canje de recompensas', 'Tienda visual'],
  },
  {
    name: 'Logros',
    title: 'Logros',
    moduleLabel: 'logros',
    description: 'Vitrina de insignias obtenidas y próximas a desbloquear.',
    plannedFeatures: ['Insignias permanentes', 'Progreso de descubrimiento'],
  },
];

function ParentModeFab(): React.JSX.Element {
  const navigation = useNavigation();

  return (
    <Pressable
      accessibilityRole="button"
      accessibilityLabel="Modo padre"
      onPress={() => navigation.navigate('ParentMode' as never)}
      style={styles.fab}
    >
      <AppText variant="caption" color="inverse">
        Padre
      </AppText>
    </Pressable>
  );
}

function createTabScreen(config: (typeof TAB_SCREENS)[number]) {
  return function TabScreen(): React.JSX.Element {
    return (
      <View style={styles.tabRoot}>
        <DevStatusScreen
          title={config.title}
          moduleLabel={config.moduleLabel}
          description={config.description}
          plannedFeatures={config.plannedFeatures}
        />
        <ParentModeFab />
      </View>
    );
  };
}

function LogoutButton(): React.JSX.Element {
  const { logout, loading } = useAuth();

  return (
    <Pressable
      accessibilityRole="button"
      accessibilityLabel="Cerrar sesión"
      onPress={() => void logout()}
      disabled={loading}
      style={styles.logout}
    >
      <AppText variant="caption" style={styles.logoutText}>
        Salir
      </AppText>
    </Pressable>
  );
}

export function MainTabNavigator(): React.JSX.Element {
  const user = useAppStore((state) => state.user);

  return (
    <Tab.Navigator
      screenOptions={{
        headerShown: true,
        tabBarActiveTintColor: theme.semantic.tabActive,
        tabBarInactiveTintColor: theme.semantic.tabInactive,
        tabBarStyle: styles.tabBar,
        headerStyle: styles.header,
        headerTitleStyle: theme.typography.h3,
        headerRight: () => <LogoutButton />,
        animation: 'shift',
      }}
    >
      {TAB_SCREENS.map((config) => (
        <Tab.Screen
          key={config.name}
          name={config.name}
          component={createTabScreen(config)}
          options={{
            title: config.name === 'Home' && user ? `Hola, ${user.nombre}` : config.title,
            tabBarAccessibilityLabel: `Pestaña ${config.title}`,
          }}
        />
      ))}
    </Tab.Navigator>
  );
}

const styles = StyleSheet.create({
  tabRoot: {
    flex: 1,
  },
  tabBar: {
    borderTopColor: theme.semantic.border,
    backgroundColor: theme.semantic.surface,
  },
  header: {
    backgroundColor: theme.semantic.surface,
  },
  fab: {
    position: 'absolute',
    right: theme.spacing.md,
    bottom: theme.spacing.md,
    backgroundColor: theme.colors.secondary[500],
    borderRadius: theme.radii.pill,
    paddingHorizontal: theme.spacing.md,
    paddingVertical: theme.spacing.xs,
    ...theme.shadows.md,
  },
  logout: {
    marginRight: theme.spacing.md,
    minHeight: 44,
    minWidth: 44,
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoutText: {
    color: theme.colors.error,
  },
});
