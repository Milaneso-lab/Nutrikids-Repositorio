import AsyncStorage from '@react-native-async-storage/async-storage';

export interface LocalStorageAdapter {
  getItem(key: string): Promise<string | null>;
  setItem(key: string, value: string): Promise<void>;
  removeItem(key: string): Promise<void>;
  getJson<T>(key: string): Promise<T | null>;
  setJson<T>(key: string, value: T): Promise<void>;
}

export const localStorage: LocalStorageAdapter = {
  getItem: (key) => AsyncStorage.getItem(key),
  setItem: (key, value) => AsyncStorage.setItem(key, value),
  removeItem: (key) => AsyncStorage.removeItem(key),
  async getJson<T>(key: string): Promise<T | null> {
    const raw = await AsyncStorage.getItem(key);
    if (!raw) {
      return null;
    }
    try {
      return JSON.parse(raw) as T;
    } catch {
      return null;
    }
  },
  async setJson<T>(key: string, value: T): Promise<void> {
    await AsyncStorage.setItem(key, JSON.stringify(value));
  },
};

/**
 * Cola offline para hábitos (07_AppMovil.md §11).
 * Solo estructura; la sincronización se implementará en fases posteriores.
 */
export interface OfflineQueueItem {
  id: string;
  endpoint: string;
  payload: Record<string, unknown>;
  createdAt: string;
}

export type OfflineQueue = OfflineQueueItem[];
