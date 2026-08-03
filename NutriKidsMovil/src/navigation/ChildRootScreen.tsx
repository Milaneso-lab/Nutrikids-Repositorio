import React from 'react';

import { KidThemeProvider } from '@features/nino/providers/KidThemeProvider';

import { ChildNavigator } from './ChildNavigator';

export function ChildRootScreen(): React.JSX.Element {
  return (
    <KidThemeProvider>
      <ChildNavigator />
    </KidThemeProvider>
  );
}
