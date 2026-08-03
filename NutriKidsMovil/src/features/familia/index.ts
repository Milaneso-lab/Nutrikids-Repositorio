/** Centro de Administración Familiar — Épica 3 */
export const FAMILIA_FEATURE = 'familia' as const;

export { FamilyDashboardScreen } from './screens/FamilyDashboardScreen';
export { ChildFormScreen } from './screens/ChildFormScreen';
export { ChildProfileScreen } from './screens/ChildProfileScreen';

export { ninosService } from './services/ninosService';
export type { Nino, NinoWithPuntos, FamilySummary } from './types/familia.types';
