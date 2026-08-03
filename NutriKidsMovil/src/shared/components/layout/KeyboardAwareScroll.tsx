import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useRef,
  useState,
} from 'react';
import {
  Dimensions,
  Keyboard,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  View,
  type ScrollViewProps,
  type TextInput,
  type ViewStyle,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

import { theme } from '@core/theme';

interface KeyboardScrollContextValue {
  scrollToFocused: (input: TextInput) => void;
}

const KeyboardScrollContext = createContext<KeyboardScrollContextValue | null>(null);

export function useKeyboardScroll(): KeyboardScrollContextValue | null {
  return useContext(KeyboardScrollContext);
}

interface KeyboardAwareScrollProps extends ScrollViewProps {
  children: React.ReactNode;
  contentContainerStyle?: ViewStyle;
  keyboardVerticalOffset?: number;
}

export function KeyboardAwareScroll({
  children,
  contentContainerStyle,
  keyboardVerticalOffset,
  ...scrollProps
}: KeyboardAwareScrollProps): React.JSX.Element {
  const insets = useSafeAreaInsets();
  const scrollRef = useRef<ScrollView>(null);
  const contentRef = useRef<View>(null);
  const scrollYRef = useRef(0);
  const lastFocusedRef = useRef<TextInput | null>(null);
  const [keyboardHeight, setKeyboardHeight] = useState(0);

  useEffect(() => {
    const showEvent = Platform.OS === 'ios' ? 'keyboardWillShow' : 'keyboardDidShow';
    const hideEvent = Platform.OS === 'ios' ? 'keyboardWillHide' : 'keyboardDidHide';

    const showSub = Keyboard.addListener(showEvent, (event) => {
      setKeyboardHeight(event.endCoordinates.height);
    });
    const hideSub = Keyboard.addListener(hideEvent, () => {
      setKeyboardHeight(0);
    });

    return () => {
      showSub.remove();
      hideSub.remove();
    };
  }, []);

  const offset = keyboardVerticalOffset ?? insets.top + (Platform.OS === 'ios' ? 44 : 56);
  const bottomInset =
    keyboardHeight > 0
      ? keyboardHeight - insets.bottom + theme.spacing['2xl']
      : theme.spacing.lg;

  const scrollToFocused = useCallback(
    (input: TextInput) => {
      const contentNode = contentRef.current;
      if (!contentNode) {
        return;
      }

      lastFocusedRef.current = input;

      requestAnimationFrame(() => {
        input.measureLayout(
          contentNode,
          (_left, top, _width, height) => {
            const windowHeight = Dimensions.get('window').height;
            const visibleHeight = windowHeight - keyboardHeight - offset - theme.spacing.lg;
            const inputBottom = top + height;
            const targetScroll = inputBottom - visibleHeight + theme.spacing.md;

            if (targetScroll > scrollYRef.current) {
              scrollRef.current?.scrollTo({
                y: Math.max(0, targetScroll),
                animated: true,
              });
            }
          },
          () => undefined,
        );
      });
    },
    [keyboardHeight, offset],
  );

  useEffect(() => {
    if (keyboardHeight > 0 && lastFocusedRef.current) {
      scrollToFocused(lastFocusedRef.current);
    }
  }, [keyboardHeight, scrollToFocused]);

  return (
    <KeyboardScrollContext.Provider value={{ scrollToFocused }}>
      <KeyboardAvoidingView
        style={styles.flex}
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        keyboardVerticalOffset={offset}
      >
        <ScrollView
          ref={scrollRef}
          keyboardShouldPersistTaps="handled"
          keyboardDismissMode="interactive"
          automaticallyAdjustKeyboardInsets={Platform.OS === 'ios'}
          showsVerticalScrollIndicator={false}
          nestedScrollEnabled
          scrollEventThrottle={16}
          onScroll={(event) => {
            scrollYRef.current = event.nativeEvent.contentOffset.y;
            scrollProps.onScroll?.(event);
          }}
          contentContainerStyle={[styles.content, { paddingBottom: bottomInset }, contentContainerStyle]}
          {...scrollProps}
        >
          <View ref={contentRef} collapsable={false}>
            {children}
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </KeyboardScrollContext.Provider>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  content: {
    flexGrow: 1,
  },
});
