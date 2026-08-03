import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

import { DevStatusScreen } from '@shared/components/layout/DevStatusScreen';
import { useAppStore } from '@state/stores/appStore';

import { ParentModeStackParamList } from './types';

const Stack = createNativeStackNavigator<ParentModeStackParamList>();

export function ParentModeNavigator(): React.JSX.Element {
  const user = useAppStore((state) => state.user);

  return (
    <Stack.Navigator screenOptions={{ animation: 'slide_from_bottom' }}>
      <Stack.Screen name="ParentDashboard" options={{ title: 'Modo padre' }}>
        {() => (
          <DevStatusScreen
            title="Panel del padre"
            moduleLabel="modo-padre"
            description={
              user
                ? `Sesión activa de ${user.nombre}. Aquí el padre verá el progreso agregado de sus hijos.`
                : 'Vista de supervisión parental sin datos clínicos crudos.'
            }
            plannedFeatures={[
              'Progreso semanal en lenguaje amigable',
              'Gestión de perfiles de niños',
              'Coordinación con el nutriólogo',
            ]}
          />
        )}
      </Stack.Screen>
    </Stack.Navigator>
  );
}
