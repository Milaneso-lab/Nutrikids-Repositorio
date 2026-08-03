import type { ReminderKind } from '../types/communication.types';

export const CATEGORY_EMOJI: Record<string, string> = {
  logro: '🏆',
  habito: '✅',
  reto: '🎯',
  recompensa: '🎁',
  recordatorio: '⏰',
  familiar: '💌',
  evento: '🎉',
  profesional: '👩‍⚕️',
};

export const PARENT_MESSAGE_TEMPLATES = [
  { id: 'congrats', label: '¡Felicitaciones!', emoji: '🎉', content: '¡Estoy muy orgulloso de ti! Sigue así.' },
  { id: 'proud', label: '¡Qué orgullo!', emoji: '⭐', content: 'Cada día me sorprendes más. ¡Eres increíble!' },
  { id: 'love', label: 'Te quiero mucho', emoji: '💚', content: 'Recuerda que te apoyo en todo. ¡Vamos juntos!' },
  { id: 'great_job', label: '¡Buen trabajo!', emoji: '👏', content: 'Vi tu esfuerzo hoy. ¡Sigue cuidándote!' },
] as const;

export const VIRTUAL_REWARDS = [
  { id: 'star', label: 'Estrella dorada', emoji: '⭐' },
  { id: 'heart', label: 'Corazón especial', emoji: '💛' },
  { id: 'trophy', label: 'Trofeo virtual', emoji: '🏆' },
  { id: 'rainbow', label: 'Arcoíris de alegría', emoji: '🌈' },
] as const;

/** Recordatorios positivos — nunca culpa ni presión */
export const REMINDER_TEMPLATES: Record<
  ReminderKind,
  { emoji: string; messages: string[]; defaultHour: number; defaultMinute: number }
> = {
  hidratacion: {
    emoji: '💧',
    messages: [
      '¡Hora de hidratarse! Tu cuerpo te lo agradecerá',
      '¿Qué tal un vasito de agua? ¡Tú puedes!',
      'Tu compañero te recuerda: agua = energía',
    ],
    defaultHour: 10,
    defaultMinute: 0,
  },
  alimentacion: {
    emoji: '🍎',
    messages: [
      '¡Momento de comer algo nutritivo y rico!',
      'Las frutas y verduras te hacen súper fuerte',
      '¿Listo para una merienda saludable?',
    ],
    defaultHour: 12,
    defaultMinute: 30,
  },
  actividad: {
    emoji: '🏃',
    messages: [
      '¡Hora de moverse! Bailar también cuenta',
      'Un poco de actividad te llena de energía',
      '¿Jugamos un rato al aire libre?',
    ],
    defaultHour: 16,
    defaultMinute: 0,
  },
  sueno: {
    emoji: '🌙',
    messages: [
      'Tu cuerpo necesita descansar para mañana ser genial',
      'Hora de prepararse para dormir bien',
      'Un buen sueño te hace crecer fuerte y feliz',
    ],
    defaultHour: 20,
    defaultMinute: 30,
  },
  mision: {
    emoji: '🎯',
    messages: [
      '¡Tienes misiones divertidas esperándote!',
      '¿Completamos una misión juntos hoy?',
      'Cada misión te acerca a una nueva aventura',
    ],
    defaultHour: 17,
    defaultMinute: 0,
  },
};

export const SEED_CAMPAIGNS = [
  {
    id: 'camp-semana-salud',
    kind: 'semanal' as const,
    title: 'Semana de la Salud',
    description: 'Completa hábitos todos los días esta semana',
    emoji: '🌱',
    rewardXp: 50,
    rewardCoins: 25,
  },
  {
    id: 'camp-familia-unida',
    kind: 'familiar' as const,
    title: 'Reto Familiar',
    description: 'Toda la familia cuidándose juntos',
    emoji: '👨‍👩‍👧',
    rewardXp: 100,
    rewardCoins: 50,
  },
  {
    id: 'camp-regreso-clases',
    kind: 'escolar' as const,
    title: 'Regreso a Clases',
    description: 'Hábitos saludables para el cole',
    emoji: '📚',
    rewardXp: 75,
    rewardCoins: 30,
  },
];

export const MASCOT_DELIVERY_PREFIX = 'Tu compañero te dice: ';

/** Palabras prohibidas — mensajes no deben generar culpa */
export const NEGATIVE_PATTERNS = [
  /deberías/i,
  /tienes que/i,
  /mal/i,
  /castigo/i,
  /fracas/i,
  /perezoso/i,
  /gordo/i,
  /flaco/i,
];
