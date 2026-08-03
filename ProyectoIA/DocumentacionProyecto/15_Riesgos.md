# 15 — Matriz de Riesgos

> Complementa los riesgos ya listados en `02_Arquitectura.md` §7 (riesgos de la arquitectura en sí). Este documento cubre riesgos de proyecto, de datos, de producto y de seguridad de forma transversal. Escala: Probabilidad (P) y Severidad (S) en Baja/Media/Alta.

---

## Riesgos técnicos

| Riesgo | P | S | Mitigación |
|---|---|---|---|
| Migración `pacientes`/`infantes` → `ninos` pierde o asigna mal el `padre_id` de registros existentes | Media | Alta | Migración en dos pasos: primero se detectan y marcan explícitamente los registros sin `padre_id` inferible, nunca se asigna uno arbitrario; requiere resolución humana antes de cerrar T2.2 (`13_Backlog.md`) |
| Migración de propiedad de esquema (Laravel→Alembic, ADR-003) rompe el entorno de desarrollo de algún agente a mitad de trabajo | Media | Media | Comunicar el corte en `Bitacora.md` antes de ejecutarlo; ventana de migración con ambos sistemas leyendo el mismo esquema durante la transición |
| Rate limiting distribuido (Redis) mal implementado permite bypass si Redis cae | Baja | Alta | Diseñar fail-closed (si Redis no responde, se aplica el límite más restrictivo por defecto, nunca "sin límite") — se documenta explícitamente en la implementación de T1.2 |
| Latencia añadida por convertir Laravel/Flask en clientes HTTP de la API (ADR-002) degrada UX del backoffice | Baja | Media | Red interna de baja latencia (misma VPC/Docker network); cache Redis para lecturas repetidas; medir antes/después con la prueba de carga de `10_Pruebas.md` §5 |
| Tablas de alto volumen (`habito_registros`, `alertas`) crecen sin índices/particionado adecuado una vez la app móvil tenga uso real | Media | Media | Índices ya definidos desde el diseño (`03_BaseDatos.md` §8); monitoreo de tamaño de tabla vía Prometheus antes de que se vuelva un problema de performance |

## Riesgos de seguridad

| Riesgo | P | S | Mitigación |
|---|---|---|---|
| Filtración de expediente clínico de un menor por fallo de RBAC por fila | Baja | Muy alta | Filtro por fila obligatorio a nivel de repositorio de datos (`05_Seguridad.md` §2), no opcional; tests de integración específicos que verifican 404 cross-tenant (`10_Pruebas.md`) |
| Token JWT de un niño usado para acceder a endpoints de adulto (escalación de privilegio) | Baja | Alta | Scope explícito en el JWT del niño (claim de tipo de cuenta), verificado en cada endpoint sensible, no solo por rol genérico |
| Secretos filtrados por error humano (commit accidental de `.env`) | Media | Alta | `.gitignore` ya cubre `.env` (verificado); se añade pre-commit hook de detección de secretos como mejora recomendada en `13_Backlog.md` (Fase 1) |
| Dependencias con vulnerabilidades conocidas sin parchear (Laravel/FastAPI/npm) | Media | Media | Escaneo automático en CI (`11_Deployment.md` §2.5), actualización periódica programada, no reactiva |
| Ataque de fuerza bruta contra el PIN corto del niño | Media | Media | Bloqueo progresivo tras pocos intentos (más agresivo que el de adultos, dado el espacio de PIN reducido); nunca se compensa con un PIN más largo que sacrifique usabilidad infantil |

## Riesgos de producto / negocio

| Riesgo | P | S | Mitigación |
|---|---|---|---|
| La app móvil no logra enganchar a los niños (gamificación insuficiente o mal calibrada) | Media | Alta | MVP acotado (Fase 4 de `12_Roadmap.md`) para validar con usuarios reales antes de invertir en el sistema completo de retos/logros (Fase 5) |
| Percepción negativa por parte de padres/nutriólogos si la app expone datos corporales al niño de forma insensible | Baja | Alta | Decisión de diseño explícita ya tomada (`07_AppMovil.md` §8: el niño nunca ve peso/IMC crudo) — riesgo mitigado por diseño, no por política post-hoc |
| Dependencia de tres stacks distintos (PHP/Python/futuro TS) dificulta encontrar/mantener talento | Media | Media | Contrato de API como frontera dura (`04_API.md`) permite que equipos/agentes distintos trabajen cada capa con conocimiento acotado, sin necesitar dominar los tres stacks a la vez |
| Alcance del ecosistema (web+API+móvil+cloud+monitoreo) es ambicioso para el ritmo real de desarrollo disponible | Alta | Media | Roadmap por fases con puntos estables (`12_Roadmap.md`) — el proyecto puede pausarse entre fases sin quedar en estado roto |

## Riesgos de cumplimiento / datos de menores

| Riesgo | P | S | Mitigación |
|---|---|---|---|
| Recolección de datos de menores sin marco de consentimiento formalizado si el proyecto escala a producción real con usuarios reales | Media (si escala) | Alta | Diseño ya alineado preventivamente con principios de minimización y consentimiento parental (`05_Seguridad.md` §7); se recomienda validación legal formal antes de captar usuarios reales fuera de contexto académico |
| Backups o logs contienen PII de menores sin cifrado/retención adecuada | Baja | Alta | Cifrado de backups (`05_Seguridad.md` §9), scrubbing de PII en logs de error (`05_Seguridad.md` §8) |

## Cómo se usa esta matriz

Antes de iniciar cualquier fase de `12_Roadmap.md`, se revisan los riesgos de esta tabla relevantes a esa fase y se confirma que la mitigación sigue vigente o se actualiza. Un riesgo materializado (ocurrió de verdad) se registra en `Bitacora.md` con la fecha y la respuesta tomada, y esta tabla se actualiza si cambia su probabilidad/severidad futura.
