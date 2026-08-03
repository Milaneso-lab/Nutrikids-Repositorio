import type { ComingSoonContent, ComingSoonFeature } from '../types/nino.types';

export const COMING_SOON_CONTENT: Record<ComingSoonFeature, ComingSoonContent> = {
  retos: {
    emoji: '🎯',
    title: 'Mis Retos',
    subtitle: '¡Misiones épicas te esperan!',
    teaser: 'Pronto podrás completar retos diarios y ganar puntos extra.',
  },
  logros: {
    emoji: '🏆',
    title: 'Mis Logros',
    subtitle: 'Tu vitrina de héroe',
    teaser: 'Colecciona insignias especiales por tus logros nutricionales.',
  },
  habitos: {
    emoji: '✅',
    title: 'Mis Hábitos',
    subtitle: 'Pequeños pasos, grandes cambios',
    teaser: 'Marca tus hábitos del día y mira cómo crece tu racha.',
  },
  alimentacion: {
    emoji: '🍎',
    title: 'Mi Alimentación',
    subtitle: 'Aventuras deliciosas',
    teaser: 'Descubre menús y consejos divertidos sobre comida saludable.',
  },
  configuracion: {
    emoji: '⚙️',
    title: 'Configuración',
    subtitle: 'Tu espacio, a tu manera',
    teaser: 'Pronto podrás ajustar sonidos, colores y preferencias.',
  },
  tienda: {
    emoji: '🛍️',
    title: 'Tienda',
    subtitle: 'Canjea tus puntos',
    teaser: 'Personaliza tu avatar con recompensas que ganarás jugando.',
  },
};
