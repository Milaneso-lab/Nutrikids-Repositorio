-- Diagnóstico de integridad y salud del esquema NutriKids.
-- Ejecutar desde DBCode, pgAdmin o:
--   docker compose exec -T postgres psql -U nutrikids_user -d nutrikids -f /dev/stdin < sql/diagnostico_integridad.sql
-- Todas las consultas son de solo lectura.

\echo '== 1. Claves foráneas sin índice de apoyo (deberia estar vacio) =='
SELECT c.conrelid::regclass AS tabla,
       a.attname           AS columna,
       c.confrelid::regclass AS referencia
FROM pg_constraint c
JOIN LATERAL unnest(c.conkey) k(attnum) ON true
JOIN pg_attribute a ON a.attrelid = c.conrelid AND a.attnum = k.attnum
WHERE c.contype = 'f'
  AND NOT EXISTS (
      SELECT 1 FROM pg_index i
      WHERE i.indrelid = c.conrelid AND a.attnum = i.indkey[0]
  )
ORDER BY 1, 2;

\echo '== 2. Restricciones CHECK declaradas =='
SELECT conrelid::regclass AS tabla,
       conname            AS restriccion,
       pg_get_constraintdef(oid) AS definicion
FROM pg_constraint
WHERE contype = 'c'
  AND connamespace = 'public'::regnamespace
ORDER BY 1, 2;

\echo '== 3. Volumen por tabla =='
SELECT relname AS tabla,
       n_live_tup AS filas_estimadas,
       pg_size_pretty(pg_total_relation_size(relid)) AS tamano_total
FROM pg_stat_user_tables
ORDER BY pg_total_relation_size(relid) DESC;

\echo '== 4. Indices nunca usados (candidatos a revision, requiere trafico real) =='
SELECT relname AS tabla,
       indexrelname AS indice,
       idx_scan AS lecturas,
       pg_size_pretty(pg_relation_size(indexrelid)) AS tamano
FROM pg_stat_user_indexes
WHERE idx_scan = 0
  AND indexrelname NOT LIKE '%_pkey'
ORDER BY pg_relation_size(indexrelid) DESC;

\echo '== 5. Huerfanos logicos: ninos sin padre valido =='
SELECT n.id, n.nombre, n.padre_id
FROM ninos n
LEFT JOIN usuarios u ON u.id_usuario = n.padre_id
WHERE u.id_usuario IS NULL;

\echo '== 6. Evaluaciones sin nino ni paciente asociado =='
SELECT id, fecha_evaluacion, nino_id, paciente_id
FROM evaluaciones
WHERE nino_id IS NULL AND paciente_id IS NULL;

\echo '== 7. Usuarios por rol =='
SELECT r.nombre AS rol, COUNT(u.id_usuario) AS usuarios
FROM roles r
LEFT JOIN usuarios u ON u.rol_id = r.id
GROUP BY r.nombre
ORDER BY r.nombre;

\echo '== 8. Estado de migraciones =='
SELECT 'alembic' AS motor, version_num AS revision FROM alembic_version
UNION ALL
SELECT 'laravel', MAX(migration) FROM migrations;
