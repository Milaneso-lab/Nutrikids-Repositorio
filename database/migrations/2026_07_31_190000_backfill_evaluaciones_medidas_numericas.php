<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Las evaluaciones creadas desde el panel web guardaban la medición sólo en las
 * columnas de texto `peso`/`talla`, así que quedaban invisibles para la API y la
 * app móvil, que leen `peso_kg`/`talla_cm`/`imc`. Este relleno normaliza el
 * histórico; el modelo Evaluacion ya deriva estas columnas en cada escritura nueva.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Sólo filas con medidas de texto parseables y sin equivalente numérico.
        DB::statement(<<<'SQL'
            WITH normalizadas AS (
                SELECT
                    id,
                    NULLIF(replace(trim(peso), ',', '.'), '')::numeric  AS peso_num,
                    NULLIF(replace(trim(talla), ',', '.'), '')::numeric AS talla_num
                FROM evaluaciones
                WHERE (peso_kg IS NULL OR talla_cm IS NULL OR imc IS NULL)
                  AND peso  ~ '^\s*[0-9]+([.,][0-9]+)?\s*$'
                  AND talla ~ '^\s*[0-9]+([.,][0-9]+)?\s*$'
            ), calculadas AS (
                SELECT
                    id,
                    peso_num,
                    -- La talla se capturaba indistintamente en metros (1.25) o centímetros (125).
                    CASE WHEN talla_num > 3 THEN talla_num ELSE talla_num * 100 END AS talla_cm_num,
                    CASE WHEN talla_num > 3 THEN talla_num / 100 ELSE talla_num END AS talla_m_num
                FROM normalizadas
                WHERE peso_num > 0 AND talla_num > 0
            )
            UPDATE evaluaciones e
            SET peso_kg  = COALESCE(e.peso_kg, ROUND(c.peso_num, 2)),
                talla_cm = COALESCE(e.talla_cm, ROUND(c.talla_cm_num, 2)),
                imc      = COALESCE(e.imc, ROUND(c.peso_num / (c.talla_m_num * c.talla_m_num), 2))
            FROM calculadas c
            WHERE e.id = c.id
              AND c.talla_m_num > 0
              AND ROUND(c.peso_num / (c.talla_m_num * c.talla_m_num), 2) BETWEEN 0.01 AND 99.99
        SQL);
    }

    public function down(): void
    {
        // Relleno de datos: no se revierte para no destruir mediciones válidas.
    }
};
