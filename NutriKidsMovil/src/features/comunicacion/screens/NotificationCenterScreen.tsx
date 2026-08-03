import React from 'react';
import {
  ActivityIndicator,
  Pressable,
  RefreshControl,
  ScrollView,
  Text,
  View,
} from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import Animated, { FadeInDown } from 'react-native-reanimated';

import { KidCard } from '@features/nino/components/KidCard';
import { KidScreenBackground } from '@features/nino/components/KidScreenBackground';
import { useThemedKidStyles } from '@features/nino/hooks/useThemedKidStyles';
import { useKidTheme } from '@features/nino/providers/KidThemeProvider';
import type { ChildStackParamList } from '@navigation/types';

import { EventCard } from '../components/EventCard';
import { NotificationCard } from '../components/NotificationCard';
import { useCampaigns, useNotificationCenter } from '../hooks/useNotificationCenter';
import type { NotificationCategory } from '../types/communication.types';

type Props = NativeStackScreenProps<ChildStackParamList, 'NotificationCenter'>;

const FILTERS: Array<{ key: NotificationCategory | 'all'; label: string; emoji: string }> = [
  { key: 'all', label: 'Todas', emoji: '📬' },
  { key: 'familiar', label: 'Familia', emoji: '💌' },
  { key: 'logro', label: 'Logros', emoji: '🏆' },
  { key: 'habito', label: 'Hábitos', emoji: '✅' },
  { key: 'recordatorio', label: 'Recordatorios', emoji: '⏰' },
  { key: 'evento', label: 'Eventos', emoji: '🎉' },
];

export function NotificationCenterScreen({ navigation }: Props): React.JSX.Element {
  const { colors, gradients } = useKidTheme();
  const styles = useThemedKidStyles((t) => ({
    content: { padding: t.spacing.lg, paddingBottom: t.spacing['3xl'], gap: t.spacing.md },
    title: { fontFamily: t.fonts.extraBold, fontSize: 26, color: t.colors.textOnGradient },
    subtitle: { fontFamily: t.fonts.medium, fontSize: 14, color: t.colors.textOnGradientMuted },
    filters: { gap: t.spacing.xs, paddingVertical: t.spacing.xs },
    filterChip: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: 4,
      paddingHorizontal: 12,
      paddingVertical: 8,
      borderRadius: t.radii.pill,
      backgroundColor: t.colors.surfaceElevated,
      minHeight: 44,
    },
    filterChipActive: { backgroundColor: t.colors.surface },
    filterEmoji: { fontSize: 16 },
    filterLabel: { fontFamily: t.fonts.semiBold, fontSize: 12, color: t.colors.inkSoft },
    filterLabelActive: { color: t.colors.grape },
    markAll: { alignSelf: 'flex-end', minHeight: 44, justifyContent: 'center' },
    markAllText: { fontFamily: t.fonts.semiBold, fontSize: 13, color: t.colors.textOnGradient },
    list: { gap: t.spacing.sm },
    loader: { marginVertical: t.spacing.xl },
    empty: {
      fontFamily: t.fonts.medium,
      fontSize: 14,
      color: t.colors.textOnGradient,
      textAlign: 'center',
      padding: t.spacing.lg,
    },
    section: { gap: t.spacing.sm },
    sectionTitle: { fontFamily: t.fonts.extraBold, fontSize: 18, color: t.colors.textOnGradient },
    linkButton: { minHeight: 48, justifyContent: 'center' },
    linkText: { fontFamily: t.fonts.bold, fontSize: 15, color: t.colors.ink, textAlign: 'center' },
    error: { fontFamily: t.fonts.medium, fontSize: 13, color: t.colors.textOnGradient, textAlign: 'center' },
  }));

  const {
    notifications,
    unreadCount,
    loading,
    error,
    activeFilter,
    setActiveFilter,
    markRead,
    markAllRead,
    refresh,
  } = useNotificationCenter();
  const { campaigns } = useCampaigns();

  return (
    <KidScreenBackground gradient={gradients.profile}>
      <ScrollView
        contentContainerStyle={styles.content}
        refreshControl={
          <RefreshControl refreshing={loading} onRefresh={refresh} tintColor={colors.textOnGradient} />
        }
      >
        <Animated.View entering={FadeInDown.springify()}>
          <Text style={styles.title}>Mis Notificaciones 📬</Text>
          <Text style={styles.subtitle}>
            {unreadCount > 0 ? `${unreadCount} sin leer` : '¡Estás al día!'}
          </Text>
        </Animated.View>

        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.filters}>
          {FILTERS.map((f) => (
            <Pressable
              key={f.key}
              accessibilityRole="button"
              accessibilityState={{ selected: activeFilter === f.key }}
              onPress={() => setActiveFilter(f.key)}
              style={[styles.filterChip, activeFilter === f.key && styles.filterChipActive]}
            >
              <Text style={styles.filterEmoji}>{f.emoji}</Text>
              <Text style={[styles.filterLabel, activeFilter === f.key && styles.filterLabelActive]}>{f.label}</Text>
            </Pressable>
          ))}
        </ScrollView>

        {unreadCount > 0 ? (
          <Pressable accessibilityRole="button" onPress={() => void markAllRead()} style={styles.markAll}>
            <Text style={styles.markAllText}>Marcar todas como leídas</Text>
          </Pressable>
        ) : null}

        {loading && notifications.length === 0 ? (
          <ActivityIndicator size="large" color={colors.textOnGradient} style={styles.loader} />
        ) : (
          <View style={styles.list}>
            {notifications.map((n, i) => (
              <NotificationCard
                key={n.id}
                notification={n}
                index={i}
                onPress={() => void markRead(n.id)}
              />
            ))}
            {notifications.length === 0 ? (
              <Text style={styles.empty}>Aquí aparecerán tus logros y mensajes positivos 🌈</Text>
            ) : null}
          </View>
        )}

        {campaigns.length > 0 ? (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Eventos activos</Text>
            {campaigns.map((c) => (
              <EventCard key={c.id} campaign={c} />
            ))}
          </View>
        ) : null}

        <KidCard style={styles.linkButton}>
          <Pressable accessibilityRole="button" onPress={() => navigation.navigate('ChildMessages')}>
            <Text style={styles.linkText}>💌 Ver mensajes de mi familia</Text>
          </Pressable>
        </KidCard>

        <KidCard style={styles.linkButton}>
          <Pressable accessibilityRole="button" onPress={() => navigation.navigate('RemindersSettings')}>
            <Text style={styles.linkText}>⏰ Configurar recordatorios</Text>
          </Pressable>
        </KidCard>

        {error ? <Text style={styles.error}>{error}</Text> : null}
      </ScrollView>
    </KidScreenBackground>
  );
}
