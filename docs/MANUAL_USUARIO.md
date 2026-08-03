# Manual de Usuario — NutriKids

## Roles

### Padre / tutor

**Web (Flask — http://localhost:5000)**

- Consultar contenido educativo sobre nutrición infantil
- Usar calculadora IMC
- Contactar al equipo
- Participar en foros y comentarios

**App móvil**

1. Registrarse o iniciar sesión
2. En **Centro familiar**: ver y crear perfiles de hijos
3. Entrar en **Modo niño** para que el niño use la app (temporal hasta login PIN)
4. Enviar **mensajes de ánimo** al niño
5. Configurar **recordatorios** positivos

### Niño

**App móvil (Modo niño)**

- Ver mascota y progreso (nivel, monedas, energía)
- Completar **hábitos diarios** (agua, actividad, alimentación, etc.)
- Revisar **calendario** y **estadísticas**
- Leer **mensajes** de la familia en la mascota
- Centro de **notificaciones** con logros y recordatorios

> La experiencia infantil usa lenguaje positivo, sin castigos ni comparaciones entre niños.

### Nutriólogo

**Web (Laravel — http://localhost:8080)**

- Gestionar pacientes
- Crear evaluaciones antropométricas
- Diseñar menús
- Generar reportes PDF
- Administrar citas

### Administrador

**Web (Laravel)**

- Gestionar usuarios y nutriólogos
- Moderar contenido, foros y comentarios
- Configuración general
- Gestión de citas globales

## Primeros pasos — App móvil

1. Instalar Expo Go o build nativo
2. Crear cuenta de padre
3. Añadir perfil del niño (nombre, fecha nacimiento, avatar)
4. Pulsar **Modo niño** en el perfil del hijo
5. Explorar hábitos desde la pantalla principal

## Modo demostración

Para pruebas sin servidor, activar demo en configuración de desarrollo (`EXPO_PUBLIC_DEMO_MODE=true`).

Credenciales: `demo@nutrikids.app` / `Demo1234`

## Soporte

- Problemas técnicos: ver `docs/GUIA_INSTALACION.md`
- Contenido clínico: consultar nutriólogo asignado

## Accesibilidad

- Tipografía Nunito legible
- Botones con área táctil ≥44pt
- Contraste en tema claro infantil
- Mejoras WCAG completas planificadas en Roadmap v2
