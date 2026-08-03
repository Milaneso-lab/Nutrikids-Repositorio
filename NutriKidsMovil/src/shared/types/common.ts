export type Nullable<T> = T | null;

export type ID = string | number;

export interface BaseEntity {
  id: ID;
  createdAt?: string;
  updatedAt?: string;
}

export type LoadingState = 'idle' | 'loading' | 'success' | 'error';

export interface Paginated<T> {
  items: T[];
  total: number;
  page: number;
  pageSize: number;
}

export type SessionPhase = 'bootstrap' | 'unauthenticated' | 'child' | 'parent';

export type FeatureModule =
  | 'auth'
  | 'home'
  | 'habitos'
  | 'retos'
  | 'avatar'
  | 'logros'
  | 'modo-padre'
  | 'nino'
  | 'familia'
  | 'progresion';
