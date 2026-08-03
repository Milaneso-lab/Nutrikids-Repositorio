import React, { useState } from 'react';
import { Modal, Pressable, Text, View } from 'react-native';
import type { CompositeNavigationProp } from '@react-navigation/native';
import { useNavigation } from '@react-navigation/native';
import type { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';

import type { AvatarConfig } from '@features/familia/types/familia.types';
import type { ChildStackParamList, ChildTabParamList } from '@navigation/types';
import { useThemeStore } from '@state/stores/themeStore';

import { useChildProfileView } from '../hooks/useChildProfileView';
import { useThemedKidStyles } from '../hooks/useThemedKidStyles';
import { KidAvatarDisplay } from './KidAvatarDisplay';
import { KidConfirmDialog } from './KidConfirmDialog';
import { KidText } from './KidText';

type ChildNavigation = CompositeNavigationProp<
  BottomTabNavigationProp<ChildTabParamList, 'Inicio'>,
  NativeStackNavigationProp<ChildStackParamList>
>;

interface ChildHeaderProfileMenuProps {
  avatarConfig: AvatarConfig | null;
  childName: string;
}

interface MenuItem {
  id: string;
  label: string;
  icon: string;
  onPress: () => void;
  destructive?: boolean;
}

export function ChildHeaderProfileMenu({
  avatarConfig,
  childName,
}: ChildHeaderProfileMenuProps): React.JSX.Element {
  const navigation = useNavigation<ChildNavigation>();
  const { exitChildMode, isStandalone } = useChildProfileView();
  const colorScheme = useThemeStore((state) => state.colorScheme);
  const toggleColorScheme = useThemeStore((state) => state.toggleColorScheme);
  const [menuOpen, setMenuOpen] = useState(false);
  const [confirmLogout, setConfirmLogout] = useState(false);
  const [loggingOut, setLoggingOut] = useState(false);
  const styles = useThemedKidStyles((t) => ({
      backdrop: {
        flex: 1,
        backgroundColor: 'rgba(0, 0, 0, 0.35)',
        alignItems: 'flex-end',
        paddingTop: t.spacing.lg,
        paddingRight: t.spacing.md,
      },
      panel: {
        width: 280,
        backgroundColor: t.colors.surfaceElevated,
        borderRadius: t.radii.card,
        paddingVertical: t.spacing.sm,
        ...t.shadow.card,
        borderWidth: 1,
        borderColor: t.colors.border,
      },
      userBlock: {
        paddingHorizontal: t.spacing.md,
        paddingVertical: t.spacing.sm,
        gap: 2,
      },
      divider: {
        height: 1,
        backgroundColor: t.colors.border,
        marginVertical: t.spacing.xxs,
      },
      menuItem: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: t.spacing.sm,
        paddingHorizontal: t.spacing.md,
        paddingVertical: t.spacing.md,
        minHeight: 48,
      },
      menuItemPressed: {
        backgroundColor: t.colors.menuPressed,
      },
      menuIcon: {
        fontSize: 18,
        width: 24,
        textAlign: 'center',
      },
      destructiveLabel: {
        color: t.colors.coral,
      },
    }));

  function closeMenu(): void {
    setMenuOpen(false);
  }

  const menuItems: MenuItem[] = [
    {
      id: 'settings',
      label: 'Configuración de perfil',
      icon: '⚙️',
      onPress: () => {
        closeMenu();
        navigation.navigate('ChildProfileEdit');
      },
    },
    {
      id: 'theme',
      label: colorScheme === 'dark' ? 'Modo claro' : 'Modo oscuro',
      icon: colorScheme === 'dark' ? '☀️' : '🌙',
      onPress: () => {
        void toggleColorScheme();
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
        hitSlop={8}
      >
        <KidAvatarDisplay avatarConfig={avatarConfig} size="sm" />
      </Pressable>

      <Modal visible={menuOpen} transparent animationType="fade" onRequestClose={closeMenu}>
        <Pressable style={styles.backdrop} onPress={closeMenu} accessibilityRole="button" accessibilityLabel="Cerrar menú">
          <Pressable style={styles.panel} onPress={(event) => event.stopPropagation()}>
            <View style={styles.userBlock}>
              <KidText bold>{childName}</KidText>
              <KidText tone="secondary" size={12}>
                Mi perfil
              </KidText>
            </View>

            <View style={styles.divider} />

            {menuItems.map((item) => (
              <Pressable
                key={item.id}
                accessibilityRole="button"
                onPress={item.onPress}
                style={({ pressed }) => [styles.menuItem, pressed ? styles.menuItemPressed : null]}
              >
                <Text style={styles.menuIcon}>{item.icon}</Text>
                <KidText
                  size={14}
                  style={item.destructive ? styles.destructiveLabel : undefined}
                >
                  {item.label}
                </KidText>
              </Pressable>
            ))}
          </Pressable>
        </Pressable>
      </Modal>

      <KidConfirmDialog
        visible={confirmLogout}
        title="Cerrar sesión"
        message={
          isStandalone
            ? '¿Deseas salir de tu cuenta?'
            : '¿Deseas volver al modo padre?'
        }
        confirmLabel="Cerrar sesión"
        cancelLabel="Cancelar"
        destructive
        loading={loggingOut}
        onCancel={() => setConfirmLogout(false)}
        onConfirm={() => {
          setLoggingOut(true);
          void exitChildMode().finally(() => {
            setLoggingOut(false);
            setConfirmLogout(false);
          });
        }}
      />
    </>
  );
}
