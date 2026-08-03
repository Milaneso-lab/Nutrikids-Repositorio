/** Experiencia móvil del niño — Épica 4 (UI + estructura) */
export const NINO_FEATURE = 'nino' as const;

export { ChildHomeScreen } from './screens/ChildHomeScreen';
export { ChildProfileScreen } from './screens/ChildProfileScreen';
export { ChildProfileEditScreen } from './screens/ChildProfileEditScreen';
export { AvatarEditorScreen } from './screens/AvatarEditorScreen';
export { ChildMoreScreen } from './screens/ChildMoreScreen';
export { ChildComingSoonScreen } from './screens/ChildComingSoonScreen';

export { KidThemeProvider, useKidTheme } from './providers/KidThemeProvider';
export { getKidTheme, kidTheme } from './theme/kidTheme';
export type { KidColorScheme, KidTheme as KidThemeType, KidThemeColors } from './theme/kidTheme';

export { useChildSessionStore } from './store/childSessionStore';
export { mapNinoToChildProfile, childProfileService } from './services/childProfileService';
export type { ChildProfile, ComingSoonFeature } from './types/nino.types';
