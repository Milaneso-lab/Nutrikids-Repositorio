import { useEffect, useState } from 'react';
import NetInfo from '@react-native-community/netinfo';

export function useNetworkStatus(): { isConnected: boolean; isInternetReachable: boolean } {
  const [status, setStatus] = useState({ isConnected: true, isInternetReachable: true });

  useEffect(() => {
    const unsubscribe = NetInfo.addEventListener((state) => {
      setStatus({
        isConnected: state.isConnected ?? false,
        isInternetReachable: state.isInternetReachable ?? state.isConnected ?? false,
      });
    });

    return unsubscribe;
  }, []);

  return status;
}
