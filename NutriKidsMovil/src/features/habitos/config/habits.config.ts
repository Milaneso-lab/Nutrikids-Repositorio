import type { AgeHabitRecommendation, HabitoCategoria } from '../types/habits.types';

/** Mapeo icono catálogo → emoji amigable para niños */
export const HABIT_ICON_EMOJI: Record<string, string> = {
  agua: '💧',
  verduras: '🥦',
  frutas: '🍎',
  correr: '🏃',
  luna: '🌙',
  manos: '🧼',
  pantalla: '📵',
  default: '✨',
};

export const HABIT_CATEGORY_COLORS: Record<HabitoCategoria, string> = {
  alimentacion: '#6EE7B7',
  actividad: '#7DD3FC',
  sueno: '#C4B5FD',
  higiene: '#FDE047',
};

/** Mensajes siempre positivos — nunca culpa ni castigo */
export const POSITIVE_HABIT_MESSAGES = [
  '¡Qué bien cuidas tu cuerpo!',
  '¡Cada paso cuenta!',
  '¡Tu compañero está orgulloso de ti!',
  '¡Sigue así, campeón!',
  '¡Eres un héroe de la salud!',
  '¡Tu cuerpo te lo agradece!',
] as const;

export const PET_REACTION_MESSAGES = {
  complete: [
    '¡Lo lograste! Me haces muy feliz 💚',
    '¡Qué hábito tan genial completaste!',
    '¡Vamos juntos! Cada hábito nos hace más fuertes',
    '¡Eres increíble! Sigamos cuidándonos',
  ],
  partial: [
    '¡Vas muy bien! Cada hábito suma',
    '¡Ya casi! Tú puedes con lo que falta',
    '¡Me encanta ver tu esfuerzo!',
  ],
  welcome: [
    '¡Hola! Hoy es un gran día para cuidarnos',
    '¿Listo para una aventura saludable?',
  ],
} as const;

/** Recomendaciones por edad — motivación intrínseca, sin presión */
export const AGE_HABIT_RECOMMENDATIONS: AgeHabitRecommendation[] = [
  { minAge: 3, maxAge: 5, dailyTarget: 2, message: 'Pequeños pasos, grandes logros' },
  { minAge: 6, maxAge: 8, dailyTarget: 3, message: 'Tres hábitos al día te hacen súper fuerte' },
  { minAge: 9, maxAge: 11, dailyTarget: 4, message: 'Cuatro hábitos diarios, ¡tú puedes!' },
  { minAge: 12, maxAge: 17, dailyTarget: 5, message: 'Cinco hábitos saludables, eres un ejemplo' },
];

export const DEFAULT_DAILY_TARGET = 3;

export function resolveHabitEmoji(icono: string): string {
  return HABIT_ICON_EMOJI[icono] ?? HABIT_ICON_EMOJI.default ?? '✨';
}
