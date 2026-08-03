import { LinkingOptions } from '@react-navigation/native';

import { RootStackParamList } from './types';

export const linking: LinkingOptions<RootStackParamList> = {
  prefixes: ['nutrikids://'],
  config: {
    screens: {
      Auth: {
        screens: {
          Splash: 'splash',
          Onboarding: 'onboarding',
          Welcome: 'welcome',
          Login: 'login',
          ChildLogin: 'child-login',
          Register: 'register',
          ForgotPassword: 'forgot-password',
          ResetPassword: {
            path: 'reset-password',
            parse: {
              email: (value: string) => decodeURIComponent(value),
              token: (value: string) => value,
            },
          },
        },
      },
      Child: {
        screens: {
          ChildTabs: {
            screens: {
              Inicio: 'nino/inicio',
              Perfil: 'nino/perfil',
              Retos: 'nino/retos',
              Logros: 'nino/logros',
              Mas: 'nino/mas',
            },
          },
          AvatarEditor: 'nino/avatar',
          HabitsHome: 'nino/habitos',
          HabitCalendar: 'nino/habitos/calendario',
          HabitStatistics: 'nino/habitos/estadisticas',
          NotificationCenter: 'nino/notificaciones',
          ChildMessages: 'nino/mensajes',
          RemindersSettings: 'nino/recordatorios',
          ComingSoon: 'nino/proximamente/:feature',
          GamePlay: 'nino/retos/:gameId',
        },
      },
      Family: {
        screens: {
          FamilyDashboard: 'familia',
          ChildForm: 'familia/hijo/formulario',
          ChildProfile: 'familia/hijo/:ninoId',
          SendFamilyMessage: 'familia/hijo/:ninoId/mensaje',
        },
      },
      Main: {
        screens: {
          Home: 'home',
          Habitos: 'habitos',
          Retos: 'retos',
          Avatar: 'avatar',
          Logros: 'logros',
        },
      },
      ParentMode: {
        screens: {
          ParentDashboard: 'parent/dashboard',
        },
      },
    },
  },
};
