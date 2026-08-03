<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `ninos` es la entidad canónica de la app móvil (docs/esquema-datos.md,
 * sql/schema_postgres.sql) pero nunca se había migrado a Laravel: solo existía
 * en el resumen SQL estático. Sin esta tabla, `php artisan migrate` sobre una
 * base vacía revienta en la migración de backfill de `evaluaciones` (columnas
 * numéricas inexistentes) y los modelos Nino/Evaluacion/Menu/User quedan con
 * columnas fillable que no existen en la base de datos.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ninos')) {
            Schema::create('ninos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('padre_id')->constrained('usuarios', 'id_usuario')->cascadeOnDelete();
                $table->foreignId('nutriologo_asignado_id')->nullable()->constrained('usuarios', 'id_usuario')->nullOnDelete();
                $table->string('nombre', 100);
                $table->string('apellidos', 100);
                $table->date('fecha_nacimiento');
                $table->string('sexo', 20);
                $table->decimal('peso_actual_kg', 5, 2)->nullable();
                $table->decimal('talla_actual_cm', 5, 2)->nullable();
                $table->jsonb('avatar_config')->nullable();
                $table->string('codigo_vinculacion', 12)->nullable()->unique();
                $table->boolean('requiere_vinculacion_padre')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('nino_credenciales')) {
            Schema::create('nino_credenciales', function (Blueprint $table) {
                $table->foreignId('nino_id')->primary()->constrained('ninos')->cascadeOnDelete();
                $table->string('pin_hash');
                $table->string('dispositivo_id')->nullable();
                $table->timestamp('vinculado_en')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('usuarios') && ! Schema::hasColumn('usuarios', 'rol_id')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->foreignId('rol_id')->nullable()->after('rol')->constrained('roles')->restrictOnDelete();
            });
        }

        if (Schema::hasTable('evaluaciones')) {
            Schema::table('evaluaciones', function (Blueprint $table) {
                if (! Schema::hasColumn('evaluaciones', 'nino_id')) {
                    $table->foreignId('nino_id')->nullable()->after('paciente_id')->constrained('ninos')->cascadeOnDelete();
                }
                if (! Schema::hasColumn('evaluaciones', 'nutriologo_id')) {
                    $table->foreignId('nutriologo_id')->nullable()->after('nino_id')->constrained('usuarios', 'id_usuario')->nullOnDelete();
                }
                if (! Schema::hasColumn('evaluaciones', 'peso_kg')) {
                    $table->decimal('peso_kg', 5, 2)->nullable()->after('talla');
                }
                if (! Schema::hasColumn('evaluaciones', 'talla_cm')) {
                    $table->decimal('talla_cm', 5, 2)->nullable()->after('peso_kg');
                }
                if (! Schema::hasColumn('evaluaciones', 'imc')) {
                    $table->decimal('imc', 4, 2)->nullable()->after('talla_cm');
                }
                if (! Schema::hasColumn('evaluaciones', 'percentil_oms')) {
                    $table->decimal('percentil_oms', 5, 2)->nullable()->after('imc');
                }
                if (! Schema::hasColumn('evaluaciones', 'fecha_evaluacion')) {
                    $table->date('fecha_evaluacion')->nullable()->after('recomendaciones');
                }
            });
        }

        if (Schema::hasTable('menus') && ! Schema::hasColumn('menus', 'nino_id')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->foreignId('nino_id')->nullable()->after('paciente_id')->constrained('ninos')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menus', 'nino_id')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->dropConstrainedForeignId('nino_id');
            });
        }

        if (Schema::hasTable('evaluaciones')) {
            Schema::table('evaluaciones', function (Blueprint $table) {
                foreach (['fecha_evaluacion', 'percentil_oms', 'imc', 'talla_cm', 'peso_kg'] as $col) {
                    if (Schema::hasColumn('evaluaciones', $col)) {
                        $table->dropColumn($col);
                    }
                }
                if (Schema::hasColumn('evaluaciones', 'nutriologo_id')) {
                    $table->dropConstrainedForeignId('nutriologo_id');
                }
                if (Schema::hasColumn('evaluaciones', 'nino_id')) {
                    $table->dropConstrainedForeignId('nino_id');
                }
            });
        }

        if (Schema::hasColumn('usuarios', 'rol_id')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropConstrainedForeignId('rol_id');
            });
        }

        Schema::dropIfExists('nino_credenciales');
        Schema::dropIfExists('ninos');
    }
};
