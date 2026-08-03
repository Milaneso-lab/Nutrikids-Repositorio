import type { ProgressionSnapshot } from '../types/progression.types';

export interface IProgressionRepository {
  load(ninoId: number): Promise<ProgressionSnapshot | null>;
  save(snapshot: ProgressionSnapshot): Promise<void>;
  syncFromRemote(ninoId: number, local: ProgressionSnapshot): Promise<ProgressionSnapshot>;
}
