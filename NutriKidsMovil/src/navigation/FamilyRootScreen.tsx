import React from 'react';

import { ParentThemeProvider } from '@features/familia/providers/ParentThemeProvider';
import { FamilyNavigator } from './FamilyNavigator';

export function FamilyRootScreen(): React.JSX.Element {
  return (
    <ParentThemeProvider>
      <FamilyNavigator />
    </ParentThemeProvider>
  );
}
