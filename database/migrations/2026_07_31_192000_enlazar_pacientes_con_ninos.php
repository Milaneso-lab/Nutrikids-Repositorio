<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El panel web guarda el expediente clínico en `pacientes` y la app móvil
 * trabaja sobre `ninos`, que es la entidad canónica (hábitos, logros, puntos y
 * citas cuelgan de ella). Las tablas puente `evaluaciones` y `menus` ya tenían
 * las dos claves foráneas, pero el panel sólo rellenaba `paciente_id`, así que
 * las mediciones y los planes del nutriólogo nunca llegaban a la app del padre.
 *
 * Esta migración añade el enlace explícito `pacientes.nino_id`, empareja el
 * histórico por nombre y fecha de nacimiento, y propaga `nino_id` a las filas
 * ya existentes de evaluaciones y menús.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pacientes') || ! Schema::hasTable('ninos')) {
            return;
        }

        if (! Schema::hasColumn('pacientes', 'nino_id')) {
            Schema::table('pacientes', function (Blueprint $table) {
                $table->unsignedBigInteger('nino_id')->nullable()->after('id');
                // Un expediente por niño: evita duplicar la historia clínica.
                $table->unique('nino_id');
                $table->foreign('nino_id')->references('id')->on('ninos')->nullOnDelete();
            });
        }

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Empareja expedientes con niños por nombre, apellidos y fecha de
        // nacimiento, sólo cuando la correspondencia es única por ambos lados.
        DB::statement(<<<'SQL'
            WITH candidatos AS (
                SELECT p.id AS paciente_id, n.id AS nino_id
                FROM pacientes p
                JOIN ninos n
                  ON lower(trim(n.nombre)) = lower(trim(p.nombre))
                 AND lower(trim(coalesce(n.apellidos, ''))) = lower(trim(coalesce(p.apellidos, '')))
                 AND n.fecha_nacimiento = p.fecha_nacimiento::date
                WHERE p.nino_id IS NULL
                  AND n.deleted_at IS NULL
                  AND p.fecha_nacimiento IS NOT NULL
            ), unicos AS (
                SELECT paciente_id, MIN(nino_id) AS nino_id
                FROM candidatos
                GROUP BY paciente_id
                HAVING count(*) = 1
            ), sin_colision AS (
                SELECT paciente_id, nino_id
                FROM unicos u
                WHERE NOT EXISTS (SELECT 1 FROM pacientes p2 WHERE p2.nino_id = u.nino_id)
                  AND (SELECT count(*) FROM unicos u2 WHERE u2.nino_id = u.nino_id) = 1
            )
            UPDATE pacientes p
            SET nino_id = s.nino_id
            FROM sin_colision s
            WHERE p.id = s.paciente_id
        SQL);

        // Propaga el enlace al histórico de mediciones y planes alimenticios.
        foreach (['evaluaciones', 'menus'] as $tabla) {
            if (! Schema::hasColumn($tabla, 'nino_id')) {
                continue;
            }

            DB::statement(<<<SQL
                UPDATE {$tabla} t
                SET nino_id = p.nino_id
                FROM pacientes p
                WHERE t.paciente_id = p.id
                  AND t.nino_id IS NULL
                  AND p.nino_id IS NOT NULL
            SQL);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pacientes', 'nino_id')) {
            Schema::table('pacientes', function (Blueprint $table) {
                $table->dropForeign(['nino_id']);
                $table->dropUnique(['nino_id']);
                $table->dropColumn('nino_id');
            });
        }
    }
};
