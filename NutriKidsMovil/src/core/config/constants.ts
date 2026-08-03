export const APP_NAME = 'NutriKids';

export const STORAGE_KEYS = {
  deviceId: '@nutrikids/device_id',
  offlineQueue: '@nutrikids/offline_queue',
  selectedProfileId: '@nutrikids/selected_profile_id',
  onboardingCompleted: '@nutrikids/onboarding_completed',
  userSession: '@nutrikids/user_session',
  childSessionMeta: '@nutrikids/child_session_meta',
  parentColorScheme: '@nutrikids/parent_color_scheme',
} as const;

export const SECURE_KEYS = {
  accessToken: 'nutrikids.access_token',
  refreshToken: 'nutrikids.refresh_token',
  childAccessToken: 'nutrikids.child_access_token',
  childRefreshToken: 'nutrikids.child_refresh_token',
  childSession: 'nutrikids.child_session',
} as const;

export const HTTP_TIMEOUT_MS = 30_000;

export const NAVIGATION_ANIMATION_DURATION = 250;
