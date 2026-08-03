# 07 — Diseño Completo de la Aplicación Móvil (React Native)

> Depende de: [`03_BaseDatos.md`](./03_BaseDatos.md) §6 (dominio gamificación), [`04_API.md`](./04_API.md), [`05_Seguridad.md`](./05_Seguridad.md) §7 (datos de menores).
> **No incluye código.** Es el diseño funcional y de producto que cualquier agente debe usar como especificación antes de escribir una sola pantalla.

---

## 1. Principio de producto

La app móvil **no es la web en un contenedor móvil**. Es un producto distinto, dirigido directamente al niño (no al padre), cuyo objetivo es convertir hábitos saludables en una experiencia de juego. El padre tiene un rol de supervisión, no de uso diario de la app del niño (el padre gestiona desde la web o desde una vista "modo padre" dentro de la misma app — ver §7).

**Tono de diseño**: colorido, positivo, sin culpa ni comparación negativa (nunca "estás gordo" o rankings públicos de peso) — el enfoque es hábito y esfuerzo, no resultado corporal, por sensibilidad del público infantil y para evitar reforzar ansiedad alimentaria.

---

## 2. Actores dentro de la app

- **Niño**: usuario principal, accede con PIN corto vinculado a su perfil (`nino_credenciales`, `03_BaseDatos.md` §3.5).
- **Padre**: accede a una sección separada ("Modo Padre", protegida por su propio login JWT completo) dentro de la misma app o en la web — decisión de UX en §7.

---

## 3. Mapa de pantallas y navegación

```
[Splash] → [Selección de perfil] (si el dispositivo tiene varios niños vinculados)
                │
                ▼
        [Login con PIN del niño]
                │
                ▼
   ┌─────────────────────────────┐
   │        Tab Bar principal      │
   ├───────────┬───────────┬───────┴────┬─────────────┬───────────┐
   │  Inicio    │  Hábitos   │   Retos     │   Avatar     │  Logros    │
   │ (Home)     │  del día   │             │  / Tienda    │            │
   └─────┬──────┴─────┬─────┴──────┬──────┴──────┬───────┴─────┬─────┘
         │             │            │              │             │
         ▼             ▼            ▼              ▼             ▼
   Resumen diario  Checklist    Detalle de     Personalización  Vitrina de
   + racha +       de hábitos    reto activo    de avatar +      insignias
   puntos totales  asignados     + progreso     canje de         obtenidas
                                                 recompensas

   [Icono flotante] → [Modo Padre] (requiere login de padre, sale del contexto del niño)
```

### 3.1 Detalle de pantallas

1. **Splash / Selección de perfil**: si el padre vinculó más de un hijo al mismo dispositivo, se elige el avatar del niño que va a usar la app (sin exponer nombres completos ni datos sensibles en esta pantalla — solo apodo/avatar).
2. **Login con PIN**: teclado numérico grande, sin teclado de texto completo (reduce fricción para niños pequeños y limita superficie de exposición de datos, `05_Seguridad.md` §7).
3. **Inicio (Home)**: saludo personalizado, racha de días consecutivos activa, resumen de puntos y nivel actual, hábito destacado del día ("¡Hoy toca beber 6 vasos de agua!"), acceso rápido a retos activos.
4. **Hábitos del día**: checklist visual (iconos grandes, animación de confeti al marcar completado) de los hábitos asignados por el nutriólogo (`nino_habitos`), con feedback inmediato de puntos ganados.
5. **Retos**: tarjetas de retos activos con barra de progreso (`nino_retos.progreso`), retos completados pasan a un historial; retos nuevos se destacan con notificación push.
6. **Avatar / Tienda**: personalización visual del avatar (ropa, accesorios, fondo) desbloqueada con puntos; es la manifestación visual de `ninos.avatar_config`.
7. **Logros**: vitrina de insignias obtenidas (`nino_logros`) y "próximos a desbloquear" (gamifica el descubrimiento), sin mostrar comparación con otros niños (no hay leaderboard entre niños de familias distintas — decisión de diseño, ver §8).
8. **Modo Padre**: pantalla separada, requiere reautenticación con las credenciales completas del padre (no el PIN del niño), muestra progreso del niño en términos descriptivos (no solo números crudos: "Juan cumplió 5 de 7 hábitos esta semana"), y permite gestionar hábitos/retos asignados en coordinación con lo definido por el nutriólogo.

---

## 4. Gamificación — mecánica de juego

### 4.1 Puntos
Cada hábito completado (`habito_registros`) y cada reto cumplido (`nino_retos`) otorgan puntos según `habitos_catalogo.puntos_base` / `retos_catalogo.puntos_recompensa`. El saldo vive en `nino_puntos` (`03_BaseDatos.md` §6.7), recalculado por la API tras cada evento — nunca calculado en el cliente (evita manipulación del saldo desde un dispositivo comprometido).

### 4.2 Niveles
Umbrales de puntos acumulados determinan `nivel_actual`. Subir de nivel desbloquea nuevas opciones de personalización de avatar — refuerzo positivo sin gasto de puntos, adicional al canje directo en tienda.

### 4.3 Rachas (streaks)
Días consecutivos con al menos un hábito completado. Se calcula a partir de `habito_registros` (gaps rompen la racha). Visualmente prominente en Home — es el mecanismo de retención principal (patrón validado en apps de hábitos para adultos, adaptado a tono infantil).

### 4.4 Retos
Definidos por condición flexible en JSON (`retos_catalogo.condicion`, `03_BaseDatos.md` §6.4) para que nutriólogo/admin puedan crear nuevos retos sin requerir cambios de código — p. ej. "7 días seguidos de verduras", "completa 3 hábitos distintos en un día".

### 4.5 Logros (insignias permanentes)
A diferencia de los retos (repetibles/temporales), un logro se obtiene una sola vez y queda para siempre (`nino_logros`) — ejemplos: "Primera semana completa", "100 vasos de agua acumulados", "Probé 10 verduras distintas".

### 4.6 Recompensas
Canje de puntos por recompensas definidas por el nutriólogo/admin (`recompensas_catalogo`), no necesariamente digitales — pueden representar privilegios reales acordados con el padre (p. ej. "elige el menú del sábado"), tendiendo un puente intencional entre el juego digital y el hábito familiar real.

---

## 5. Seguimiento de hábitos y progreso

- Registro diario simple: un toque para marcar un hábito como completado (`POST /ninos/{id}/habitos/{id}/registrar`, `04_API.md`), con confirmación visual inmediata.
- Vista de progreso semanal/mensual en formato amigable (no gráficas técnicas de adulto): calendario con días marcados, no ejes ni porcentajes crudos.
- Los datos clínicos (peso, talla, IMC, percentil) **no se muestran al niño** — son visibles solo en Modo Padre y en el panel del nutriólogo. La app del niño trabaja exclusivamente con la capa de gamificación (`03_BaseDatos.md` §6), nunca con `evaluaciones` directamente.

---

## 6. Avatar

- `ninos.avatar_config` (JSONB) almacena la configuración visual (color de piel/cabello, ropa, accesorios, fondo desbloqueado). Estructura libre a propósito para permitir añadir nuevos ítems de personalización sin migración de esquema.
- El avatar es la identidad visual del niño dentro de la app — sustituye cualquier necesidad de foto real o dato identificable en pantallas compartidas (refuerza minimización de datos, `05_Seguridad.md` §7).

---

## 7. Sincronización con la cuenta del padre

- **Vinculación**: el padre genera un `codigo_vinculacion` (`03_BaseDatos.md` §3.4) desde la web o desde Modo Padre en la propia app; el niño (o el padre en su nombre, la primera vez) introduce ese código al instalar la app para vincular el dispositivo (`POST /ninos/{id}/vincular-dispositivo`, `04_API.md`).
- **Multi-dispositivo**: un niño puede tener el código vigente en un único dispositivo activo a la vez (se invalida el anterior al vincular uno nuevo) — evita cuentas fantasma abiertas en dispositivos perdidos.
- **Multi-hijo**: un padre con más de un hijo ve un selector de perfil en la app (pantalla de Splash, §3.1); cada hijo mantiene su propio progreso, avatar y puntos de forma completamente independiente.
- **Datos que sí cruzan la frontera niño↔padre**: progreso agregado, rachas, logros obtenidos (visibles para el padre en descriptivo, no en crudo). **Datos que nunca cruzan**: notas privadas del nutriólogo (`notas_nutriologo.privada = true`), que tampoco llegan al niño.

---

## 8. Decisiones de diseño explícitas (para que ningún agente las reintroduzca por error)

| Decisión | Razón |
|---|---|
| Sin leaderboard entre niños de familias distintas | Evita comparación social negativa entre menores; el "otro" contra quien compite el niño es su propio progreso pasado (racha, nivel), no otros usuarios |
| Sin publicidad ni compras reales dentro de la app | Protección de menores, alineado con `05_Seguridad.md` §7 |
| Sin chat libre entre niños | Fuera de alcance de producto; cualquier feature social futura requiere moderación adulta explícita, no se implementa sin ese diseño previo |
| El niño nunca ve datos clínicos crudos (peso/IMC) | Bienestar psicológico infantil; esos datos son para el padre/nutriólogo, la app del niño solo gamifica el hábito, no el resultado corporal |
| PIN corto, no contraseña adulta | Usabilidad para el rango de edad objetivo + minimización de datos (`05_Seguridad.md` §7) |

---

## 9. API necesaria (resumen — detalle completo en `04_API.md`)

Endpoints ya cubiertos en el diseño de la API: `POST /ninos/{id}/vincular-dispositivo`, `GET /habitos-catalogo`, `GET/POST /ninos/{id}/habitos`, `POST /ninos/{id}/habitos/{id}/registrar`, `GET /retos-catalogo`, `GET /ninos/{id}/retos`, `GET /logros-catalogo`, `GET /ninos/{id}/logros`, `GET /ninos/{id}/puntos`, `GET /recompensas-catalogo`, `POST /ninos/{id}/recompensas/{id}/canjear`. Todos requieren JWT emitido para la sesión del niño (scope limitado — un token de niño no puede acceder a `notas-nutriologo` ni a `evaluaciones` en crudo, reforzado por RBAC de `05_Seguridad.md` §2).

**Notificaciones push**: requiere un endpoint adicional `POST /dispositivos/registrar-token` (FCM/APNs token) y un servicio de envío — planificado como parte de la infraestructura de mensajería futura (`02_Arquitectura.md` §mensajería, `12_Roadmap.md` fase correspondiente), no bloqueante para el MVP de la app.

---

## 10. Estructura de proyecto recomendada (React Native)

Recomendación de organización, no de código:

```
app/
  navigation/       (tab bar + stacks, Modo Padre como stack separado)
  screens/
    auth/           (selección de perfil, login PIN, login padre)
    home/
    habitos/
    retos/
    avatar/
    logros/
    modo-padre/
  components/       (componentes de UI reutilizables: tarjeta de reto, checklist de hábito, avatar renderer)
  services/
    api/            (cliente HTTP generado desde openapi.json, ver 04_API.md §5)
    auth/           (manejo de JWT + refresh token, almacenamiento seguro en Keychain/Keystore)
    notifications/  (registro de push token)
  state/            (gestión de estado — Redux Toolkit o Zustand, decisión de tooling en 14_DecisionesArquitectura.md)
  theme/            (design tokens compartidos con identidad de marca NutriKids, distintos del tema del admin/web)
```

**Almacenamiento seguro del token en el dispositivo**: Keychain (iOS) / Keystore (Android) vía `react-native-keychain` o equivalente — nunca `AsyncStorage` plano para tokens, por ser dato de menor.

## 11. Offline básico (alcance mínimo, no offline-first completo)

- El checklist de hábitos del día se puede marcar sin conexión y se sincroniza al recuperar red (cola local simple, reintento automático).
- El resto de la app (retos, tienda, logros) requiere conexión — no se justifica offline-first completo para el alcance actual del producto; se documenta como decisión de alcance, revisable si el uso real muestra conectividad intermitente frecuente en el público objetivo.

---

## 12. Acceso de padres en la app móvil (implementado 2026-07-29)

Flujo de autenticación JWT para **padres** — distinto del futuro login PIN del niño (T4.3).

### 12.1 Pantallas

| Pantalla | Propósito |
|---|---|
| Splash | Verifica sesión guardada (refresh si access expiró) |
| Onboarding | Primera vez; 6 slides; omitible |
| Bienvenida | Logo, iniciar sesión, crear cuenta, recuperar contraseña |
| Login / Registro / Forgot / Reset | Formularios con validación cliente |

### 12.2 Endpoints consumidos

Todos bajo `/api/v1/auth/*`:

- `POST /login` — `mobile: true`, refresh 90 días
- `POST /register` — solo rol `padre`
- `POST /refresh` — rotación automática en cliente
- `POST /logout` — revoca refresh + denylist JWT
- `POST /password/forgot` | `POST /password/reset`

### 12.3 Almacenamiento seguro

| Dato | Ubicación |
|---|---|
| `access_token`, `refresh_token` | `expo-secure-store` (Keychain/Keystore) |
| Perfil de sesión (id, nombre, email, exp) | AsyncStorage (no sensible) |
| Onboarding completado | AsyncStorage |

### 12.4 Navegación post-login

Tras login/registro exitoso de **padre** → `FamilyNavigator` (Centro Familiar, §13). Logout desde header del dashboard → Bienvenida. La sesión **niño** (T4.3+) usará el tab bar principal (T4.4).

El flujo PIN del niño (§3) se implementará en T4.3 sin reemplazar este acceso de padres.

---

## 13. Centro de Administración Familiar (implementado 2026-07-29)

Módulo para que el **padre** gestione perfiles de hijos. Tras login JWT (§12), la app navega a `FamilyNavigator` — no al tab bar de gamificación del niño.

### 13.1 Pantallas

| Pantalla | Ruta stack | Propósito |
|---|---|---|
| FamilyDashboard | `FamilyDashboard` | Saludo, resumen familiar, accesos rápidos, tarjetas por hijo |
| ChildForm | `ChildForm` | Registrar o editar hijo (formulario validado) |
| ChildProfile | `ChildProfile` | Detalle, progreso, placeholders retos/logros/hábitos/alimentación |

### 13.2 Componentes reutilizables (`features/familia/components/`)

| Componente | Uso |
|---|---|
| `ChildCard` | Tarjeta de hijo en dashboard |
| `FamilySummaryCard` | Resumen agregado (hijos, nivel prom., puntos) |
| `ProgressIndicator` | Barra de progreso accesible |
| `QuickActionButton` | Acciones rápidas del dashboard |
| `EmptyState` | Sin hijos registrados |
| `AvatarPicker` | Emoji presets + foto opcional (galería) |
| `SexoSelector` | Selección masculino/femenino/otro |
| `DateInputField` | Fecha de nacimiento |
| `ConfirmDialog` | Confirmación antes de eliminar |
| `PlaceholderSection` | Marcadores retos/logros/hábitos/alimentación |

### 13.3 Endpoints consumidos

| Método | Ruta | Uso |
|---|---|---|
| GET | `/api/v1/ninos?page=&per_page=` | Listar hijos del padre (paginado `{ data, meta }`) |
| POST | `/api/v1/ninos` | Crear hijo |
| GET | `/api/v1/ninos/{id}` | Detalle |
| PUT | `/api/v1/ninos/{id}` | Actualizar |
| DELETE | `/api/v1/ninos/{id}` | Soft delete (añadido en API sesión 8) |
| GET | `/api/v1/ninos/{id}/puntos` | Nivel y puntos de gamificación |

### 13.4 Validaciones implementadas

- Nombre y apellidos obligatorios (máx. 100 chars).
- Fecha de nacimiento formato ISO, no futura, edad &lt; 18 años (calculada en UI).
- Sexo obligatorio.
- Peso 0–300 kg, estatura 0–300 cm (opcionales).
- Objetivo nutricional máx. 500 chars (opcional).
- Nivel inicial 1–99 (opcional; default 1 en demo).

Campos sin columna dedicada en API se almacenan en `avatar_config`: `objetivoNutricional`, `nivelInicial`, `photoUri`.

### 13.5 Servicios y hooks

| Archivo | Responsabilidad |
|---|---|
| `services/ninosApi.ts` | Cliente HTTP tipado |
| `services/ninosService.ts` | Orquestación + reintentos + modo demo |
| `services/demoNinosService.ts` | CRUD local AsyncStorage |
| `hooks/useNinosList` | Lista + resumen + refresh |
| `hooks/useNinoDetail` | Perfil individual |
| `hooks/useNinoForm` | Formulario crear/editar |
| `hooks/useNinoDelete` | Eliminación con confirmación |

### 13.6 Navegación post-login (actualizada)

| `sessionPhase` | Navigator |
|---|---|
| `parent` | `FamilyNavigator` (Centro Familiar) |
| `child` | `ChildNavigator` (experiencia infantil — §14) |
| otro | `AuthNavigator` |

### 13.7 Modo demo

Con `EXPO_PUBLIC_DEMO_MODE=true` (default), hijos se gestionan en AsyncStorage con datos semilla. Auth demo: `demo@nutrikids.app` / `Demo1234`.

### 13.8 Riesgos / deuda técnica

- **Upload de fotos:** no hay endpoint; `photoUri` es local.
- **Estado nutricional clínico:** placeholder; IMC en épica nutrición.
- **Cliente OpenAPI generado (T4.2):** tipos manuales en `familia.types.ts` hasta T4.2.

---

## 14. Experiencia móvil infantil (implementado 2026-07-29)

Producto lúdico-educativo para el **niño** — visualmente distinto al Centro Familiar del padre (§13). Gamificación real pendiente de T4.4b; esta fase entrega UI + estructura + avatar vía API.

### 14.1 Principios UX

| Principio | Implementación |
|---|---|
| Diversión y motivación | Gradientes cálidos, emoji, animaciones Reanimated, compañero virtual |
| Seguridad infantil | Sin peso/IMC/clínica en vistas niño (ADR-008) |
| Retención diaria | Racha, CTA "Comenzar aventura", progreso visible |
| Preparación gamificación | Componentes XP, logros, hábitos, alimentación listos para datos reales |

### 14.2 Pantallas

| Pantalla | Ruta | Estado |
|---|---|---|
| Dashboard infantil | `Inicio` | ✅ Funcional (placeholders) |
| Perfil infantil | `Perfil` | ✅ Funcional |
| Editor avatar | `AvatarEditor` | ✅ Guarda vía API |
| Retos / Logros | tabs | 🔜 Próximamente |
| Hub explorar | `Mas` | ✅ Menú navegación |
| Hábitos / Alimentación / Config / Tienda | `ComingSoon` | 🔜 Próximamente |

### 14.3 Componentes reutilizables

`KidScreenBackground`, `KidAvatarDisplay`, `KidAvatarPicker`, `LevelBadge`, `ExperienceBar`, `KidProgressCard`, `KidAchievementCard`, `KidHabitCard`, `KidFoodCard`, `KidActionButton`, `CompanionMascot`, `KidComingSoonView`.

### 14.4 Tema infantil

`features/nino/theme/kidTheme.ts` — paleta vibrante independiente del tema corporativo.

### 14.5 Sesión del niño

Store `childSessionStore` + `sessionPhase === 'child'` → `ChildNavigator`. Entrada temporal: "🎮 Modo niño" desde panel padre (pre-T4.3 PIN).

### 14.6 Endpoints

`GET/PUT /api/v1/ninos/{id}`, `GET /api/v1/ninos/{id}/puntos`. Avatar en `avatar_config` incluye `companion` (mascota).

### 14.7 Preparación épicas futuras

Lista para tienda, retos diarios, calendario, confeti avanzado, push. **Motor de Progresión implementado** — ver §15.

---

## 15. Motor de Progresión (implementado 2026-07-29)

Sistema de gamificación desacoplado en `NutriKidsMovil/src/features/progresion/`. Documentación completa en [`08_Gamificacion.md`](./08_Gamificacion.md).

### 15.1 Integración app

| Elemento | Ubicación |
|---|---|
| Provider global | `AppProviders` → `ProgressionProvider` |
| Bootstrap | `useProgressionBootstrap` al activar sesión niño |
| Dashboard | `ChildHomeScreen` → `ProgressionHud` + `ProgressionDashboardSection` |
| Perfil | `ChildProfileScreen` → racha, XP, insignias, logros, mascota desde snapshot |
| Celebraciones | `ProgressionCelebrationOverlay` (nivel, XP, monedas, misión, insignia) |

### 15.2 Sistemas disponibles

XP · Niveles · Monedas · Energía · Rachas · Logros · Insignias · Misiones (diaria/semanal/especial) · Inventario · Mascota (evolución, humor, accesorios).

### 15.3 Consumo desde otros módulos

```typescript
import { useProgression, progressionEngine, missionService } from '@features/progresion';
```

No colocar lógica de gamificación en pantallas — usar hooks y servicios del motor.

### 15.4 Sync API vs local

| Dato | Fuente |
|---|---|
| XP / nivel | API `GET /ninos/{id}/puntos` + local |
| Logros | API `GET /ninos/{id}/logros` + local |
| Monedas, energía, rachas, misiones, inventario | Local (AsyncStorage) hasta endpoints dedicados |

### 15.5 Integración hábitos (T4.5)

Ver [`09_HabitosSaludables.md`](./09_HabitosSaludables.md). El módulo `features/habitos/` consume el motor via `habitProgressionBridge` — nunca duplica lógica de XP en pantallas.

---

## 16. Hábitos Saludables (implementado 2026-07-29)

| Pantalla | Ruta | Estado |
|---|---|---|
| Mis Hábitos | `HabitsHome` | ✅ Funcional |
| Calendario | `HabitCalendar` | ✅ Funcional |
| Estadísticas | `HabitStatistics` | ✅ Funcional |

**Acceso:** `ChildMoreScreen` → Mis Hábitos; `ChildHomeScreen` → Comenzar aventura.

---

## 17. Comunicación y Acompañamiento (implementado 2026-07-29)

Ver [`10_Comunicacion.md`](./10_Comunicacion.md).

| Pantalla | Audiencia | Ruta |
|---|---|---|
| Centro de notificaciones | Niño | `NotificationCenter` |
| Mensajes familiares | Niño | `ChildMessages` |
| Recordatorios | Niño | `RemindersSettings` |
| Enviar mensaje | Padre | `SendFamilyMessage` |

Integración: `CommunicationProvider` en `AppProviders`; eventos automáticos desde Motor de Progresión.
