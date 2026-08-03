import { Platform } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as SecureStore from 'expo-secure-store';

export interface SecureStorageAdapter {
  getItem(key: string): Promise<string | null>;
  setItem(key: string, value: string): Promise<void>;
  removeItem(key: string): Promise<void>;
}

const securePrefix = 'secure:';

const webFallbackStorage: SecureStorageAdapter = {
  getItem: (key) => AsyncStorage.getItem(`${securePrefix}${key}`),
  setItem: (key, value) => AsyncStorage.setItem(`${securePrefix}${key}`, value),
  removeItem: (key) => AsyncStorage.removeItem(`${securePrefix}${key}`),
};

const nativeSecureStorage: SecureStorageAdapter = {
  async getItem(key: string): Promise<string | null> {
    try {
      return await SecureStore.getItemAsync(key);
    } catch {
      return null;
    }
  },
  async setItem(key: string, value: string): Promise<void> {
    await SecureStore.setItemAsync(key, value);
  },
  async removeItem(key: string): Promise<void> {
    await SecureStore.deleteItemAsync(key);
  },
};

export const secureStorage: SecureStorageAdapter =
  Platform.OS === 'web' ? webFallbackStorage : nativeSecureStorage;

export const secureTokenStorage = {
  get: (key: string) => secureStorage.getItem(key),
  set: (key: string, value: string) => secureStorage.setItem(key, value),
  remove: (key: string) => secureStorage.removeItem(key),
};
