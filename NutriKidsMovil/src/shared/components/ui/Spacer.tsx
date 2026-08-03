import React from 'react';
import { View } from 'react-native';

import { spacing, SpacingKey } from '@core/theme';

interface SpacerProps {
  size?: SpacingKey;
  horizontal?: boolean;
}

export function Spacer({ size = 'md', horizontal = false }: SpacerProps): React.JSX.Element {
  const value = spacing[size];
  return <View style={horizontal ? { width: value } : { height: value }} />;
}
