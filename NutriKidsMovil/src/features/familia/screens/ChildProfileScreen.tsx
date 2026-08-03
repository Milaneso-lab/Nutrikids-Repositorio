import React, { useCallback, useState } from 'react';
import {
  ActivityIndicator,
  Image,
  Pressable,
  StyleSheet,
  View,
} from 'react-native';
import * as Clipboard from 'expo-clipboard';
import { useFocusEffect } from '@react-navigation/native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { theme } from '@core/theme';
import { getFriendlyErrorMessage } from '@core/errors/friendlyMessages';
import { ParentScreen } from '../components/ParentScreen';
import { ParentCard } from '../components/ParentCard';
import { ParentText } from '../components/ParentText';
import { useParentTheme } from '../providers/ParentThemeProvider';
import { KeyboardAwareScroll } from '@shared/components/layout/KeyboardAwareScroll';
import { Button } from '@shared/components/ui/Button';
import { ErrorMessage } from '@shared/components/ui/ErrorMessage';
import { formatPoints } from '@shared/utils/format';
import { useEnterChildMode } from '@features/nino/hooks/useEnterChildMode';
import type { FamilyStackParamList } from '@navigation/types';

import { ConfirmDialog } from '../components/ConfirmDialog';
import { PlaceholderSection } from '../components/PlaceholderSection';
import { ProgressIndicator } from '../components/ProgressIndicator';
import { PinInputField } from '@features/auth/components/TextInputField';
import { useNinoDelete } from '../hooks/useNinoDelete';
import { useNinoDetail } from '../hooks/useNinoDetail';
import { ninosService } from '../services/ninosService';
import { formatAgeLabel } from '../utils/age';
import { resolveAvatar } from '../utils/avatarConfig';

type Props = NativeStackScreenProps<FamilyStackParamList, 'ChildProfile'>;

export function ChildProfileScreen({ navigation, route }: Props): React.JSX.Element {
  const { colors } = useParentTheme();
  const { ninoId } = route.params;
  const { nino, loading, error, reload } = useNinoDetail(ninoId);
  const [confirmDelete, setConfirmDelete] = useState(false);
  const [pin, setPin] = useState('');
  const [confirmarPin, setConfirmarPin] = useState('');
  const [linking, setLinking] = useState(false);
  const [linkError, setLinkError] = useState<string | null>(null);
  const [linkFieldErrors, setLinkFieldErrors] = useState<Record<string, string>>({});
  const [codeCopied, setCodeCopied] = useState(false);

  useFocusEffect(
    useCallback(() => {
      void reload();
    }, [reload]),
  );

  async function handleCopyLinkCode(code: string): Promise<void> {
    await Clipboard.setStringAsync(code);
    setCodeCopied(true);
    setTimeout(() => setCodeCopied(false), 2000);
  }

  const { deleteNino, deleting } = useNinoDelete(() => {
    setConfirmDelete(false);
    navigation.navigate('FamilyDashboard');
  });
  const { enterWithNino, loading: enteringChild } = useEnterChildMode();

  if (loading) {
    return (
      <ParentScreen>
        <View style={styles.center}>
          <ActivityIndicator size="large" color={colors.accent} />
        </View>
      </ParentScreen>
    );
  }

  if (error || !nino) {
    return (
      <ParentScreen>
        <View style={styles.center}>
          <ErrorMessage message={error ?? 'Perfil no disponible'} />
          <Button label="Reintentar" variant="secondary" onPress={() => void reload()} />
        </View>
      </ParentScreen>
    );
  }

  const avatar = resolveAvatar(nino.avatar_config);
  const puntos = nino.puntos?.puntos_totales ?? 0;
  const nivel = nino.puntos?.nivel_actual ?? 1;
  const progress = Math.min((puntos % 100) / 100, 1);
  const objetivo = nino.avatar_config?.objetivoNutricional;
  const savedLinkCode = nino.codigo_vinculacion?.trim() || null;
  const hasSavedLinkCode = Boolean(savedLinkCode);

  return (
    <ParentScreen>
      <KeyboardAwareScroll contentContainerStyle={styles.content}>
        <ParentCard style={styles.hero}>
          <View style={[styles.avatar, { backgroundColor: avatar.backgroundColor }]}>
            {avatar.photoUri ? (
              <Image source={{ uri: avatar.photoUri }} style={styles.photo} accessibilityIgnoresInvertColors />
            ) : (
              <ParentText style={styles.emoji}>{avatar.emoji}</ParentText>
            )}
          </View>
          <ParentText variant="h2" style={styles.centerText}>
            {nino.nombre} {nino.apellidos}
          </ParentText>
          <ParentText variant="bodySmall" tone="secondary" style={styles.centerText}>
            {formatAgeLabel(nino.fecha_nacimiento)} · {nino.sexo}
          </ParentText>
          <ParentText variant="caption" tone="secondary" style={styles.centerText}>
            Nivel {nivel} · {formatPoints(puntos)} puntos
          </ParentText>
          <ProgressIndicator progress={progress} label="Progreso general" />
        </ParentCard>

        <ParentCard style={styles.section}>
          <ParentText variant="h3">Información personal</ParentText>
          <InfoRow label="Peso" value={nino.peso_actual_kg != null ? `${nino.peso_actual_kg} kg` : 'No registrado'} />
          <InfoRow label="Estatura" value={nino.talla_actual_cm != null ? `${nino.talla_actual_cm} cm` : 'No registrada'} />
          <InfoRow label="Objetivo nutricional" value={objetivo ?? 'Sin objetivo definido'} />
          <InfoRow
            label="Última actividad"
            value={
              nino.updated_at
                ? new Intl.DateTimeFormat('es-MX', { dateStyle: 'medium', timeStyle: 'short' }).format(
                    new Date(nino.updated_at),
                  )
                : 'Sin registro'
            }
          />
        </ParentCard>

        <ParentCard style={styles.section}>
          <ParentText variant="h3">Estado nutricional</ParentText>
          <ParentText variant="bodySmall" tone="secondary">
            Resumen orientativo basado en datos registrados. La evaluación clínica completa estará disponible con el
            nutriólogo.
          </ParentText>
          <ParentText variant="caption" tone="secondary">
            IMC y clasificación: integración pendiente (épica nutrición).
          </ParentText>
        </ParentCard>

        <PlaceholderSection
          icon="🎯"
          title="Próximos retos"
          description="Aquí verás los retos activos asignados a este perfil."
        />
        <PlaceholderSection
          icon="🏆"
          title="Logros"
          description="Insignias y metas alcanzadas aparecerán en esta sección."
        />
        <PlaceholderSection
          icon="✅"
          title="Hábitos"
          description="Seguimiento diario de hábitos saludables del niño."
        />
        <PlaceholderSection
          icon="🍎"
          title="Alimentación"
          description="Menús, recomendaciones y registro alimenticio."
        />

        <ParentCard style={styles.section}>
          <ParentText variant="h3">Acceso del niño en su dispositivo</ParentText>
          <ParentText variant="bodySmall" tone="secondary">
            {hasSavedLinkCode
              ? 'Este código es permanente. Compártelo con tu hijo junto con su PIN para que entre en su dispositivo.'
              : 'Define un PIN y genera un código único. Comparte ambos con tu hijo para que entre sin usar tu cuenta.'}
          </ParentText>

          {hasSavedLinkCode && savedLinkCode ? (
            <View style={[styles.linkCodeBox, { backgroundColor: colors.surfaceMuted, borderColor: colors.border }]}>
              <ParentText variant="caption" tone="secondary">
                Código de vinculación de {nino.nombre}
              </ParentText>
              <View style={styles.linkCodeRow}>
                <ParentText variant="h2" style={styles.linkCode} tone="accent">
                  {savedLinkCode}
                </ParentText>
                <Pressable
                  accessibilityRole="button"
                  accessibilityLabel="Copiar código de vinculación"
                  onPress={() => void handleCopyLinkCode(savedLinkCode)}
                  style={[styles.copyButton, { backgroundColor: colors.accentSoft }]}
                >
                  <ParentText variant="h3">📋</ParentText>
                </Pressable>
              </View>
              <ParentText variant="caption" tone="secondary">
                {codeCopied ? '¡Código copiado!' : 'Toca el icono para copiar el código.'}
              </ParentText>
            </View>
          ) : null}

          <ParentText variant="label" tone="secondary">
            {hasSavedLinkCode ? 'Cambiar PIN del niño' : 'Configurar PIN del niño'}
          </ParentText>
          <PinInputField
            label="PIN del niño"
            value={pin}
            onChangeText={setPin}
            error={linkFieldErrors.pin}
            placeholder="4 a 6 números"
          />
          <PinInputField
            label="Confirmar PIN"
            value={confirmarPin}
            onChangeText={setConfirmarPin}
            error={linkFieldErrors.confirmarPin}
            placeholder="Repite el PIN"
          />
          {linkError ? <ErrorMessage message={linkError} /> : null}
          <Button
            label={
              linking
                ? hasSavedLinkCode
                  ? 'Guardando PIN…'
                  : 'Generando…'
                : hasSavedLinkCode
                  ? 'Actualizar PIN'
                  : 'Generar código y PIN'
            }
            variant="secondary"
            fullWidth
            disabled={linking}
            onPress={() => {
              setLinkError(null);
              const errors: Record<string, string> = {};
              if (!/^\d{4,6}$/.test(pin)) {
                errors.pin = 'El PIN debe tener entre 4 y 6 números';
              }
              if (pin !== confirmarPin) {
                errors.confirmarPin = 'Los PIN deben coincidir';
              }
              setLinkFieldErrors(errors);
              if (Object.keys(errors).length > 0) {
                return;
              }

              setLinking(true);
              void ninosService
                .vincularDispositivo(nino.id, { pin, confirmar_pin: confirmarPin })
                .then(() => {
                  setPin('');
                  setConfirmarPin('');
                  void reload();
                })
                .catch((err) => setLinkError(getFriendlyErrorMessage(err)))
                .finally(() => setLinking(false));
            }}
          />
        </ParentCard>

        <View style={styles.actions}>
          <Button
            label="💌 Enviar mensaje positivo"
            fullWidth
            onPress={() => navigation.navigate('SendFamilyMessage', { ninoId: nino.id, childName: nino.nombre })}
          />
          <Button
            label={enteringChild ? 'Abriendo…' : `Entrar como ${nino.nombre}`}
            fullWidth
            onPress={() => nino && void enterWithNino(nino)}
            disabled={enteringChild}
          />
          <Button
            label="Editar perfil"
            variant="secondary"
            fullWidth
            onPress={() => navigation.navigate('ChildForm', { ninoId: nino.id })}
          />
          <Button
            label="Eliminar perfil"
            variant="ghost"
            fullWidth
            onPress={() => setConfirmDelete(true)}
          />
        </View>
      </KeyboardAwareScroll>

      <ConfirmDialog
        visible={confirmDelete}
        title="Eliminar perfil"
        message={`¿Seguro que deseas eliminar el perfil de ${nino.nombre}? Esta acción no se puede deshacer.`}
        confirmLabel="Eliminar"
        destructive
        loading={deleting}
        onCancel={() => setConfirmDelete(false)}
        onConfirm={() => void deleteNino(nino.id)}
      />
    </ParentScreen>
  );
}

function InfoRow({ label, value }: { label: string; value: string }): React.JSX.Element {
  return (
    <View style={styles.infoRow}>
      <ParentText variant="caption" tone="secondary">
        {label}
      </ParentText>
      <ParentText variant="bodySmall">{value}</ParentText>
    </View>
  );
}

const styles = StyleSheet.create({
  content: {
    padding: theme.spacing.lg,
    gap: theme.spacing.md,
    paddingBottom: theme.spacing['3xl'],
  },
  center: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: theme.spacing.lg,
    gap: theme.spacing.md,
  },
  hero: {
    alignItems: 'center',
    gap: theme.spacing.sm,
  },
  centerText: {
    textAlign: 'center',
  },
  avatar: {
    width: 88,
    height: 88,
    borderRadius: theme.radii.pill,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
  },
  photo: {
    width: '100%',
    height: '100%',
  },
  emoji: {
    fontSize: 40,
  },
  section: {
    gap: theme.spacing.sm,
  },
  infoRow: {
    gap: theme.spacing.xxs,
  },
  linkCode: {
    letterSpacing: 2,
    textAlign: 'center',
    marginVertical: theme.spacing.sm,
  },
  linkCodeBox: {
    gap: theme.spacing.xxs,
    marginVertical: theme.spacing.sm,
    padding: theme.spacing.md,
    borderRadius: theme.radii.lg,
    borderWidth: 1,
  },
  linkCodeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: theme.spacing.sm,
  },
  copyButton: {
    minWidth: 44,
    minHeight: 44,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: theme.radii.md,
  },
  actions: {
    gap: theme.spacing.sm,
    marginTop: theme.spacing.sm,
  },
});
