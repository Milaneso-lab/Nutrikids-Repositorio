import React, { useState } from 'react';
import { Image, Modal, Pressable, StyleSheet, View } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';

import { theme } from '@core/theme';
import { useAuth } from '@features/auth/hooks/useAuth';
import type { FamilyStackParamList } from '@navigation/types';
import { AppText } from '@shared/components/ui/Text';
import { useAppStore } from '@state/stores/appStore';
import { useThemeStore } from '@state/stores/themeStore';

import { ConfirmDialog } from './ConfirmDialog';
import { useParentTheme } from '../providers/ParentThemeProvider';
import { resolveAvatar } from '../utils/avatarConfig';

type FamilyNavigation = NativeStackNavigationProp<FamilyStackParamList>;

interface MenuItem {
  id: string;
  label: string;
  icon: string;
  onPress: () => void;
  destructive?: boolean;
}

export function ParentHeaderMenu(): React.JSX.Element {
  const navigation = useNavigation<FamilyNavigation>();
  const user = useAppStore((state) => state.user);
  const avatarConfig = useAppStore((state) => state.avatarConfig);
  const { colors, isDark } = useParentTheme();
  const colorScheme = useThemeStore((state) => state.colorScheme);
  const toggleColorScheme = useThemeStore((state) => state.toggleColorScheme);
  const { logout, loading } = useAuth();
  const [menuOpen, setMenuOpen] = useState(false);
  const [confirmLogout, setConfirmLogout] = useState(false);

  function closeMenu(): void {
    setMenuOpen(false);
  }

  function navigateTo(screen: keyof FamilyStackParamList, params?: FamilyStackParamList[keyof FamilyStackParamList]): void {
    closeMenu();
    if (params !== undefined) {
      navigation.navigate(screen, params as never);
      return;
    }
    navigation.navigate(screen as never);
  }

  const initial = (user?.nombre?.trim().charAt(0) ?? 'P').toUpperCase();
  const avatar = resolveAvatar(avatarConfig ?? null);

  const menuItems: MenuItem[] = [
    {
      id: 'settings',
      label: 'Configuración de perfil',
      icon: '⚙️',
      onPress: () => navigateTo('ParentProfileEdit'),
    },
    {
      id: 'theme',
      label: isDark ? 'Modo claro' : 'Modo oscuro',
      icon: isDark ? '☀️' : '🌙',
      onPress: () => {
        void toggleColorScheme();
        closeMenu();
      },
    },
    {
      id: 'logout',
      label: 'Cerrar sesión',
      icon: '🚪',
      destructive: true,
      onPress: () => {
        closeMenu();
        setConfirmLogout(true);
      },
    },
  ];

  return (
    <>
      <Pressable
        accessibilityRole="button"
        accessibilityLabel="Abrir menú de perfil"
        accessibilityState={{ expanded: menuOpen }}
        onPress={() => setMenuOpen(true)}
        style={styles.trigger}
        hitSlop={8}
      >
        <View
          style={[
            styles.avatar,
            {
              backgroundColor: avatar.backgroundColor,
              borderColor: colors.surface,
            },
          ]}
        >
          {avatar.photoUri ? (
            <Image source={{ uri: avatar.photoUri }} style={styles.avatarPhoto} accessibilityIgnoresInvertColors />
          ) : avatarConfig ? (
            <AppText style={styles.avatarEmoji}>{avatar.emoji}</AppText>
          ) : (
            <AppText style={[styles.avatarInitial, { color: colors.textInverse }]}>{initial}</AppText>
          )}
        </View>
      </Pressable>

      <Modal visible={menuOpen} transparent animationType="fade" onRequestClose={closeMenu}>
        <Pressable style={styles.backdrop} onPress={closeMenu} accessibilityRole="button" accessibilityLabel="Cerrar menú">
          <Pressable
            style={[
              styles.panel,
              {
                backgroundColor: colors.surfaceElevated,
                borderColor: colors.border,
                shadowColor: colors.shadow,
              },
            ]}
            onPress={(event) => event.stopPropagation()}
          >
            <View style={styles.userBlock}>
              <AppText variant="label" style={{ color: colors.textPrimary }}>
                {user?.nombre ?? 'Padre'}
              </AppText>
              {user?.email ? (
                <AppText variant="caption" style={{ color: colors.textSecondary }} numberOfLines={1}>
                  {user.email}
                </AppText>
              ) : null}
              <AppText variant="caption" style={{ color: colors.textSecondary }}>
                Tema: {colorScheme === 'dark' ? 'Oscuro' : 'Claro'}
              </AppText>
            </View>

            <View style={[styles.divider, { backgroundColor: colors.border }]} />

            {menuItems.map((item) => (
              <Pressable
                key={item.id}
                accessibilityRole="button"
                onPress={item.onPress}
                style={({ pressed }) => [
                  styles.menuItem,
                  pressed ? { backgroundColor: colors.menuPressed } : null,
                ]}
              >
                <AppText style={styles.menuIcon}>{item.icon}</AppText>
                <AppText
                  variant="bodySmall"
                  style={[
                    { color: colors.textPrimary },
                    item.destructive ? styles.destructiveLabel : null,
                  ]}
                >
                  {item.label}
                </AppText>
              </Pressable>
            ))}
          </Pressable>
        </Pressable>
      </Modal>

      <ConfirmDialog
        visible={confirmLogout}
        title="Cerrar sesión"
        message="¿Deseas salir de tu cuenta de padre?"
        confirmLabel="Cerrar sesión"
        cancelLabel="Cancelar"
        destructive
        loading={loading}
        onCancel={() => setConfirmLogout(false)}
        onConfirm={() => {
          void logout().finally(() => setConfirmLogout(false));
        }}
      />
    </>
  );
}

const styles = StyleSheet.create({
  trigger: {
    marginRight: theme.spacing.md,
    minHeight: 44,
    minWidth: 44,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatar: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: theme.colors.primary[500],
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    overflow: 'hidden',
    ...theme.shadows.sm,
  },
  avatarInitial: {
    fontSize: 16,
    fontWeight: '700',
  },
  avatarEmoji: {
    fontSize: 18,
  },
  avatarPhoto: {
    width: '100%',
    height: '100%',
  },
  backdrop: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.35)',
    alignItems: 'flex-end',
    paddingTop: theme.spacing.sm,
    paddingRight: theme.spacing.sm,
  },
  panel: {
    width: 280,
    borderRadius: theme.radii.lg,
    paddingVertical: theme.spacing.sm,
    borderWidth: 1,
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.18,
    shadowRadius: 20,
    elevation: 8,
  },
  userBlock: {
    paddingHorizontal: theme.spacing.md,
    paddingVertical: theme.spacing.sm,
    gap: theme.spacing.xxs,
  },
  divider: {
    height: 1,
    marginVertical: theme.spacing.xxs,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: theme.spacing.sm,
    paddingHorizontal: theme.spacing.md,
    paddingVertical: theme.spacing.md,
    minHeight: 48,
  },
  menuIcon: {
    fontSize: 18,
    width: 24,
    textAlign: 'center',
  },
  destructiveLabel: {
    color: theme.colors.error,
  },
});
