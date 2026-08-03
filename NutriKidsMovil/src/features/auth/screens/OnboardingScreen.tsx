import React, { useCallback } from 'react';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { OnboardingPager } from '@features/auth/components/OnboardingPager';
import { ONBOARDING_SLIDES } from '@features/auth/constants/onboardingSlides';
import { AuthStackParamList } from '@navigation/types';
import { authService } from '@services/auth';

type Props = NativeStackScreenProps<AuthStackParamList, 'Onboarding'>;

export function OnboardingScreen({ navigation }: Props): React.JSX.Element {
  const finish = useCallback(async () => {
    await authService.markOnboardingCompleted();
    navigation.replace('Welcome');
  }, [navigation]);

  return <OnboardingPager slides={ONBOARDING_SLIDES} onComplete={() => void finish()} onSkip={() => void finish()} />;
}
