import { useCallback, useEffect, useState } from 'react';

import { AppError } from '@core/errors/AppError';
import { getFriendlyErrorMessage } from '@core/errors/friendlyMessages';
import { useAppStore } from '@state/stores/appStore';

import { ninosService } from '../services/ninosService';
import type { Nino, NinoFormValues } from '../types/familia.types';
import {
  EMPTY_NINO_FORM,
  formValuesToCreatePayload,
  formValuesToUpdatePayload,
  ninoToFormValues,
  validateNinoForm,
} from '../validation/ninoValidation';

interface UseNinoFormOptions {
  ninoId?: number;
  onSuccess?: (nino: Nino) => void;
}

export function useNinoForm({ ninoId, onSuccess }: UseNinoFormOptions) {
  const user = useAppStore((state) => state.user);
  const isEditing = Boolean(ninoId);

  const [values, setValues] = useState<NinoFormValues>(EMPTY_NINO_FORM);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [loadingInitial, setLoadingInitial] = useState(isEditing);
  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);

  useEffect(() => {
    if (!ninoId) {
      return;
    }

    let cancelled = false;

    async function loadNino() {
      setLoadingInitial(true);
      try {
        const nino = await ninosService.getById(ninoId!);
        if (!cancelled) {
          setValues(ninoToFormValues(nino));
        }
      } catch (err) {
        if (!cancelled) {
          const message =
            err instanceof AppError ? getFriendlyErrorMessage(err) : 'No pudimos cargar los datos del niño.';
          setFormError(message);
        }
      } finally {
        if (!cancelled) {
          setLoadingInitial(false);
        }
      }
    }

    void loadNino();

    return () => {
      cancelled = true;
    };
  }, [ninoId]);

  const setField = useCallback(<K extends keyof NinoFormValues>(field: K, value: NinoFormValues[K]) => {
    setValues((prev) => ({ ...prev, [field]: value }));
    setErrors((prev) => {
      if (!prev[field as string]) {
        return prev;
      }
      const next = { ...prev };
      delete next[field as string];
      return next;
    });
  }, []);

  const submit = useCallback(async () => {
    setFormError(null);
    const validation = validateNinoForm(values);
    setErrors(validation.errors);

    if (!validation.valid) {
      return null;
    }

    if (!user) {
      setFormError('Sesión no válida. Inicia sesión de nuevo.');
      return null;
    }

    setSubmitting(true);
    try {
      const nino = isEditing
        ? await ninosService.update(ninoId!, formValuesToUpdatePayload(values))
        : await ninosService.create(formValuesToCreatePayload(values, user.idUsuario));

      onSuccess?.(nino);
      return nino;
    } catch (err) {
      const message =
        err instanceof AppError ? getFriendlyErrorMessage(err) : 'No pudimos guardar los cambios. Intenta de nuevo.';
      setFormError(message);
      return null;
    } finally {
      setSubmitting(false);
    }
  }, [isEditing, ninoId, onSuccess, user, values]);

  return {
    values,
    errors,
    setField,
    setValues,
    submit,
    submitting,
    loadingInitial,
    formError,
    isEditing,
  };
}
