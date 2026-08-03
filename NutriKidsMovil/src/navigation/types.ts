import type { NavigatorScreenParams } from '@react-navigation/native';

import type { ComingSoonFeature } from '@features/nino/types/nino.types';
import type { GameId } from '@features/retos/types/challenges.types';

export type AuthStackParamList = {
  Splash: undefined;
  Onboarding: undefined;
  Welcome: undefined;
  Login: undefined;
  Register: undefined;
  ForgotPassword: undefined;
  ResetPassword: { email?: string; token?: string } | undefined;
  /** Login directo del niño (código + PIN) */
  ChildLogin: undefined;
};

export type MainTabParamList = {
  Home: undefined;
  Habitos: undefined;
  Retos: undefined;
  Avatar: undefined;
  Logros: undefined;
};

export type ChildTabParamList = {
  Inicio: undefined;
  Perfil: undefined;
  Retos: undefined;
  Logros: undefined;
  Mas: undefined;
};

export type ChildStackParamList = {
  ChildTabs: NavigatorScreenParams<ChildTabParamList>;
  ChildProfileEdit: undefined;
  AvatarEditor: undefined;
  HabitsHome: undefined;
  HabitCalendar: undefined;
  HabitStatistics: undefined;
  NotificationCenter: undefined;
  ChildMessages: undefined;
  RemindersSettings: undefined;
  ComingSoon: { feature: ComingSoonFeature };
  GamePlay: { gameId: GameId };
};

export type FamilyStackParamList = {
  FamilyDashboard: undefined;
  ParentProfileEdit: undefined;
  ChildForm: { ninoId?: number };
  ChildProfile: { ninoId: number };
  SendFamilyMessage: { ninoId: number; childName: string };
};

export type ParentModeStackParamList = {
  ParentDashboard: undefined;
};

export type RootStackParamList = {
  Auth: NavigatorScreenParams<AuthStackParamList>;
  Family: NavigatorScreenParams<FamilyStackParamList>;
  Child: NavigatorScreenParams<ChildStackParamList>;
  Main: NavigatorScreenParams<MainTabParamList>;
  ParentMode: NavigatorScreenParams<ParentModeStackParamList>;
};

declare global {
  namespace ReactNavigation {
    interface RootParamList extends RootStackParamList {}
  }
}
