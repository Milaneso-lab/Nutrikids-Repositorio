import React, { Component, ErrorInfo, ReactNode } from 'react';
import { StyleSheet, View } from 'react-native';

import { theme } from '@core/theme';
import { AppText } from '@shared/components/ui/Text';
import { Button } from '@shared/components/ui/Button';

interface Props {
  children: ReactNode;
  fallbackMessage?: string;
  onReset?: () => void;
}

interface State {
  hasError: boolean;
}

export class ErrorBoundary extends Component<Props, State> {
  state: State = { hasError: false };

  static getDerivedStateFromError(): State {
    return { hasError: true };
  }

  componentDidCatch(error: Error, info: ErrorInfo): void {
    if (__DEV__) {
      console.error('[ErrorBoundary]', error, info.componentStack);
    }
  }

  private handleReset = (): void => {
    this.setState({ hasError: false });
    this.props.onReset?.();
  };

  render(): ReactNode {
    if (!this.state.hasError) {
      return this.props.children;
    }

    return (
      <View style={styles.container} accessibilityRole="alert">
        <AppText variant="h2" style={styles.title}>
          Algo salió mal
        </AppText>
        <AppText variant="body" color="secondary" style={styles.message}>
          {this.props.fallbackMessage ?? 'Intenta de nuevo en un momento.'}
        </AppText>
        <Button label="Reintentar" onPress={this.handleReset} accessibilityHint="Restablece la pantalla" />
      </View>
    );
  }
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: theme.spacing.lg,
    backgroundColor: theme.semantic.background,
    gap: theme.spacing.md,
  },
  title: {
    textAlign: 'center',
  },
  message: {
    textAlign: 'center',
    marginBottom: theme.spacing.sm,
  },
});
