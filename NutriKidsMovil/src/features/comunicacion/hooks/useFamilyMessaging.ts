import { useCallback, useState } from 'react';

import { useAppStore } from '@state/stores/appStore';

import { isPositiveMessage, sanitizeMessage } from '../domain/validators/positiveMessageValidator';
import { familyMessageService } from '../services/communicationServices';
import { PARENT_MESSAGE_TEMPLATES, VIRTUAL_REWARDS } from '../config/communication.config';

export function useFamilyMessaging(ninoId: number, childName: string) {
  const user = useAppStore((s) => s.user);
  const [sending, setSending] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  const parentName = user?.nombre ?? 'Mamá/Papá';

  const sendTemplate = useCallback(
    async (templateId: string) => {
      const template = PARENT_MESSAGE_TEMPLATES.find((t) => t.id === templateId);
      if (!template) {
        return;
      }
      setSending(true);
      setError(null);
      setSuccess(false);
      try {
        await familyMessageService.sendFromParent({
          ninoId,
          parentName,
          content: template.content.replace('ti', childName),
          emoji: template.emoji,
          rewardType: 'felicitacion',
        });
        setSuccess(true);
      } catch {
        setError('No pudimos enviar el mensaje');
      } finally {
        setSending(false);
      }
    },
    [childName, ninoId, parentName],
  );

  const sendCustom = useCallback(
    async (content: string, emoji = '💌') => {
      const sanitized = sanitizeMessage(content);
      if (!isPositiveMessage(sanitized)) {
        setError('Usa palabras amables y positivas 💚');
        return;
      }
      setSending(true);
      setError(null);
      setSuccess(false);
      try {
        await familyMessageService.sendFromParent({
          ninoId,
          parentName,
          content: sanitized,
          emoji,
          rewardType: 'mensaje',
        });
        setSuccess(true);
      } catch {
        setError('No pudimos enviar el mensaje');
      } finally {
        setSending(false);
      }
    },
    [ninoId, parentName],
  );

  const sendVirtualReward = useCallback(
    async (rewardId: string) => {
      const reward = VIRTUAL_REWARDS.find((r) => r.id === rewardId);
      if (!reward) {
        return;
      }
      setSending(true);
      setError(null);
      setSuccess(false);
      try {
        await familyMessageService.sendFromParent({
          ninoId,
          parentName,
          content: `¡Te envío un ${reward.label} por ser tan especial!`,
          emoji: reward.emoji,
          rewardType: 'recompensa_virtual',
        });
        setSuccess(true);
      } catch {
        setError('No pudimos enviar la recompensa');
      } finally {
        setSending(false);
      }
    },
    [ninoId, parentName],
  );

  return {
    templates: PARENT_MESSAGE_TEMPLATES,
    rewards: VIRTUAL_REWARDS,
    sending,
    error,
    success,
    sendTemplate,
    sendCustom,
    sendVirtualReward,
    clearSuccess: () => setSuccess(false),
  };
}
