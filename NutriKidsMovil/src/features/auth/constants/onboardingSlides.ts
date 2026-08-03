import type { OnboardingSlideData } from '@features/auth/components/OnboardingPager';

export const ONBOARDING_SLIDES: OnboardingSlideData[] = [
  {
    id: 'intro',
    emoji: '🥦',
    title: 'Bienvenido a NutriKids',
    description: 'Una app pensada para que los niños disfruten hábitos saludables como un juego.',
  },
  {
    id: 'how',
    emoji: '🎯',
    title: '¿Cómo funciona?',
    description: 'Completa hábitos, gana puntos, sube de nivel y personaliza tu avatar con recompensas.',
  },
  {
    id: 'benefits',
    emoji: '⭐',
    title: 'Beneficios',
    description: 'Refuerzo positivo, retos divertidos y progreso visible para toda la familia.',
  },
  {
    id: 'tracking',
    emoji: '📈',
    title: 'Seguimiento infantil',
    description: 'Los padres supervisan el avance; los niños se enfocan en el juego, no en números clínicos.',
  },
  {
    id: 'security',
    emoji: '🔒',
    title: 'Seguridad',
    description: 'Sesiones protegidas con JWT, tokens seguros en el dispositivo y acceso con PIN para niños.',
  },
  {
    id: 'privacy',
    emoji: '🛡️',
    title: 'Privacidad',
    description: 'Datos de menores minimizados. Sin comparaciones públicas ni publicidad dentro de la app.',
  },
];
