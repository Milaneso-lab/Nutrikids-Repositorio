# Arquitectura Final — NutriKids (Release Candidate)

## Visión

Ecosistema de tres clientes web + una app móvil, unificados por una API REST (FastAPI) sobre PostgreSQL y Redis.

```
┌─────────────────────────────────────────────────────────────┐
│                        CLIENTES                              │
├──────────────┬──────────────┬──────────────┬────────────────┤
│ Laravel      │ Flask        │ NutriKids    │ (Futuro:       │
│ Admin/Nutri  │ Portal       │ Movil        │  Gateway TLS)  │
│ :8080        │ :5000        │ Expo/RN      │                │
└──────┬───────┴──────┬───────┴──────┬───────┴────────────────┘
       │              │              │
       └──────────────┼──────────────┘
                      ▼
            ┌─────────────────┐
            │  FastAPI :8000   │
            │  /api/v1/*       │
            │  JWT + RBAC      │
            └────────┬────────┘
                     │
         ┌───────────┴───────────┐
         ▼                       ▼
  ┌─────────────┐         ┌─────────────┐
  │ PostgreSQL  │         │   Redis     │
  │   :5432     │         │   :6379     │
  └─────────────┘         └─────────────┘
```

## Principios (ADR)

| ADR | Decisión |
|-----|----------|
| ADR-001 | PostgreSQL único motor |
| ADR-002 | FastAPI = lógica de negocio; Laravel/Flask = clientes |
| ADR-003 | Alembic dueño del esquema objetivo |
| ADR-004 | Laravel se mantiene como backoffice |
| ADR-006 | Entidad unificada `ninos` |
| ADR-008 | Sin leaderboard global entre familias |

## App móvil — arquitectura interna

```
src/
├── core/           # config, theme, api client
├── features/       # dominios (auth, familia, nino, progresion, habitos, comunicacion)
├── navigation/     # Root, Auth, Family, Child navigators
├── providers/      # AppProviders
├── services/       # auth cross-cutting
└── state/          # Zustand stores
```

**Patrón por feature:** domain → services → repositories → hooks → UI

**Event buses:**

- `progressionEventBus` — logros, XP, misiones
- `communicationEventBridge` — notificaciones derivadas

## Flujo auth móvil

```
LoginScreen → authService → FastAPI /api/v1/auth/login
           → tokenManager (SecureStore)
           → RootNavigator → FamilyNavigator | ChildNavigator
```

## Flujo hábito → progresión

```
DailyHabitTracker → habitsRepository → API (o demo local)
                 → habitProgressionBridge → ProgressionEngine
                 → progressionEventBus → communicationEventBridge
```

## Estado de migración arquitectónica

| Componente | Estado RC |
|------------|-----------|
| API v1 auth/RBAC | ✅ |
| Redis rate limit | ✅ |
| Alembic esquema | ✅ (8 revisiones) |
| Laravel → API auth | ⏳ Parcial |
| Flask → API | ⏳ Parcial |
| Gateway TLS | ❌ Pendiente |
| PIN niño | ❌ Pendiente |

## Despliegue RC

Docker Compose monolítico para demo/staging. Producción real requiere gateway TLS, secretos gestionados y BD managed (ver `09_Cloud.md`).

Documentación relacionada: `02_Arquitectura.md`, `08_Docker.md`, `17_ReleaseCandidate.md`.
