import React from 'react';

import { AuthThemeProvider } from '@features/auth/providers/AuthThemeProvider';

import { AuthNavigator } from './AuthNavigator';

export function AuthRootScreen(): React.JSX.Element {
  return (
    <AuthThemeProvider>
      <AuthNavigator />
    </AuthThemeProvider>
  );
}
