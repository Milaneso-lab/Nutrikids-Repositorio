import React, { useRef, useState } from 'react';
import {
  Dimensions,
  FlatList,
  Pressable,
  StyleSheet,
  View,
  ViewToken,
} from 'react-native';

import { theme } from '@core/theme';
import { AuthText } from '@features/auth/components/AuthText';
import { useAuthTheme } from '@features/auth/providers/AuthThemeProvider';
import { Button } from '@shared/components/ui/Button';

export interface OnboardingSlideData {
  id: string;
  emoji: string;
  title: string;
  description: string;
}

interface OnboardingPagerProps {
  slides: OnboardingSlideData[];
  onComplete: () => void;
  onSkip: () => void;
}

const { width } = Dimensions.get('window');

export function OnboardingPager({ slides, onComplete, onSkip }: OnboardingPagerProps): React.JSX.Element {
  const { colors } = useAuthTheme();
  const [index, setIndex] = useState(0);
  const listRef = useRef<FlatList<OnboardingSlideData>>(null);

  const onViewableItemsChanged = useRef(({ viewableItems }: { viewableItems: ViewToken[] }) => {
    const first = viewableItems[0];
    if (first?.index != null) {
      setIndex(first.index);
    }
  }).current;

  const goNext = (): void => {
    if (index >= slides.length - 1) {
      onComplete();
      return;
    }
    listRef.current?.scrollToIndex({ index: index + 1, animated: true });
  };

  return (
    <View style={[styles.container, { backgroundColor: colors.background }]}>
      <Pressable accessibilityRole="button" accessibilityLabel="Omitir onboarding" onPress={onSkip} style={styles.skip}>
        <AuthText variant="label" tone="accent">
          Omitir
        </AuthText>
      </Pressable>

      <FlatList
        ref={listRef}
        data={slides}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        keyExtractor={(item) => item.id}
        onViewableItemsChanged={onViewableItemsChanged}
        viewabilityConfig={{ viewAreaCoveragePercentThreshold: 60 }}
        renderItem={({ item }) => (
          <View style={[styles.slide, { width }]}>
            <AuthText variant="display" style={styles.emoji} accessibilityLabel={item.title}>
              {item.emoji}
            </AuthText>
            <AuthText variant="h2" style={styles.title}>
              {item.title}
            </AuthText>
            <AuthText variant="body" tone="secondary" style={styles.description}>
              {item.description}
            </AuthText>
          </View>
        )}
      />

      <View style={styles.footer}>
        <View style={styles.dots}>
          {slides.map((slide, dotIndex) => (
            <View
              key={slide.id}
              style={[
                styles.dot,
                { backgroundColor: colors.border },
                dotIndex === index && [styles.dotActive, { backgroundColor: colors.accent }],
              ]}
            />
          ))}
        </View>
        <Button
          label={index === slides.length - 1 ? 'Comenzar' : 'Siguiente'}
          onPress={goNext}
          fullWidth
          accessibilityHint="Avanza al siguiente paso del onboarding"
        />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  skip: {
    alignSelf: 'flex-end',
    paddingHorizontal: theme.spacing.lg,
    paddingTop: theme.spacing.md,
    minHeight: 44,
    justifyContent: 'center',
  },
  slide: {
    paddingHorizontal: theme.spacing.lg,
    paddingTop: theme.spacing['3xl'],
    alignItems: 'center',
    gap: theme.spacing.md,
  },
  emoji: {
    fontSize: 64,
    lineHeight: 72,
  },
  title: {
    textAlign: 'center',
  },
  description: {
    textAlign: 'center',
    maxWidth: 320,
  },
  footer: {
    padding: theme.spacing.lg,
    gap: theme.spacing.md,
  },
  dots: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: theme.spacing.xs,
  },
  dot: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
  dotActive: {
    width: 20,
  },
});
