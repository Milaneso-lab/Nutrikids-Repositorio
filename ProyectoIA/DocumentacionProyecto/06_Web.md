# 06 — Diseño Objetivo de la Plataforma Web

> Depende de: [`02_Arquitectura.md`](./02_Arquitectura.md) §3.2-3.3, [`04_API.md`](./04_API.md).

---

## 1. Dos aplicaciones web, no una

La plataforma web mantiene la separación funcional ya existente (verificada en la auditoría), porque responde a audiencias y requisitos no funcionales distintos:

| | Web Admin / Nutriólogo | Web Pública (Padres) |
|---|---|---|
| Audiencia | Interna (staff, ~decenas de usuarios) | Externa (miles potenciales) |
| Requisito no funcional dominante | Seguridad, auditabilidad, RBAC | SEO, performance de carga, conversión |
| Tecnología objetivo | Laravel Blade (se mantiene) | Flask (fase 1) → Next.js (fase 2) |
| Autenticación | JWT de la API, sesión de Laravel como capa de UI sobre ese JWT | JWT de la API |

## 2. Web Admin / Nutriólogo (Laravel)

- Se mantiene como está estructuralmente (rutas, Blade, RBAC de `RoleMiddleware`), con un único cambio de fondo: **las escrituras de negocio dejan de ir directo a Eloquent y pasan por la API** (`04_API.md`), según ADR-002 (`14_DecisionesArquitectura.md`).
- Controladores actuales (`Admin\*`, `Nutriologo\*`) se convierten en **clientes HTTP delgados**: reciben el request de Blade, llaman a la API con el JWT del usuario, transforman la respuesta JSON a variables de vista. Dejan de tener `Model::create()`/`Model::update()` de las entidades de negocio clínico.
- Se completa `InfanteController.php` (hoy vacío, hallazgo de auditoría) — o se elimina si tras el diseño de `03_BaseDatos.md` (`ninos` reemplaza a `Infante`/`Paciente`) ya no tiene razón de existir; decisión operativa en `16_PlanModernizacion.md`.
- Generación de PDF (`laravel-dompdf`) se mantiene en Laravel, alimentada por datos leídos de `GET /api/v1/reportes/{id}`.

## 3. Web Pública (Padres)

### 3.1 Fase 1 — Flask, desacoplado de la base de datos
- Se elimina el acceso directo a Postgres desde Flask (`SQLALCHEMY_DATABASE_URI` en `.env.flask`, verificado en la auditoría) — pasa a consumir `NUTRIKIDS_API_BASE_URL` exclusivamente, igual que ya hace parcialmente hoy.
- Se mantienen las rutas públicas actuales (`/`, `/Obesidad`, `/calculadora`, `/nutriologos`, `/Comentarios`, `/Foros`, `/conocenos`, `/login`, `/Contacto`) sin cambio de UX, solo de origen de datos.

### 3.2 Fase 2 — Migración a Next.js
Justificación (detalle en `14_DecisionesArquitectura.md`):
- **Lenguaje compartido (TypeScript)** con la app React Native → tipos y validaciones generados desde el mismo `openapi.json` (`04_API.md` §5), reduciendo divergencia entre web y móvil.
- **SSR/SSG** mejora SEO de contenido público indexable (artículos de nutrición, landing) frente a Jinja server-rendered simple.
- Permite compartir *design tokens* (colores, tipografía) con el admin si este también evoluciona a React en el futuro, y con la identidad visual de la app móvil (sin ser el mismo producto — ver `07_AppMovil.md` §1).
- Migración **por página**, no big-bang: cada ruta pública se reimplementa y se valida en paralelo antes de apagar su equivalente Flask (patrón *strangler fig*, detallado en `16_PlanModernizacion.md`).

## 4. Frontend build (Vite + Tailwind)

Se mantiene Vite + TailwindCSS 4 para los assets de Laravel Blade (ya configurado y funcional). Next.js (fase 2) trae su propio pipeline de build; no se comparte bundler entre Laravel y la web pública, cada uno usa el estándar de su framework.

## 5. Accesibilidad y UX mínima exigida

- Formularios públicos (contacto, registro, login) con validación en cliente + servidor (la de servidor ya existe vía Pydantic/Validator; la de cliente se añade en fase 2).
- Contraste y tamaños de fuente conformes a WCAG AA como estándar mínimo para contenido de salud pública.
- Diseño responsive obligatorio (móvil-primero) en la web pública, dado que buena parte del tráfico de padres será desde teléfono.

## 6. Qué no cambia en esta capa

- No se introduce un framework de estado global complejo (Redux, etc.) en el admin Laravel — sigue siendo multi-página server-rendered, adecuado a su bajo volumen de interacción.
- No se unifica Admin y Web Pública en una sola app — se mantienen separadas por las razones no funcionales de la tabla §1.
