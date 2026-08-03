<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FastAPI (NUTRIKIDS_SKIP_CREATE_ALL=1) no crea tablas por su cuenta: espera
 * que las migraciones Laravel ya hayan levantado el esquema completo. Estas
 * tablas de gamificación y refresh_tokens solo existían en el resumen SQL
 * estático (sql/schema_postgres.sql), nunca como migración real.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('refresh_tokens')) {
            Schema::create('refresh_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('usuario_id')->constrained('usuarios', 'id_usuario')->cascadeOnDelete();
                $table->string('token_hash');
                $table->string('dispositivo')->nullable();
                $table->timestamp('expira_en');
                $table->timestamp('revocado_en')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index('expira_en');
            });
        }

        if (! Schema::hasTable('habitos_catalogo')) {
            Schema::create('habitos_catalogo', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150);
                $table->text('descripcion')->nullable();
                $table->string('categoria', 20);
                $table->string('icono', 100)->nullable();
                $table->integer('puntos_base')->default(0);
                $table->boolean('activo')->default(true);
                $table->timestamps();
                $table->index('categoria');
                $table->index('activo');
            });
        }

        if (! Schema::hasTable('nino_habitos')) {
            Schema::create('nino_habitos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('nino_id')->constrained('ninos')->cascadeOnDelete();
                $table->foreignId('habito_id')->constrained('habitos_catalogo')->cascadeOnDelete();
                $table->string('frecuencia', 20);
                $table->foreignId('asignado_por_id')->nullable()->constrained('usuarios', 'id_usuario')->nullOnDelete();
                $table->boolean('activo')->default(true);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('habito_registros')) {
            Schema::create('habito_registros', function (Blueprint $table) {
                $table->id();
                $table->foreignId('nino_habito_id')->constrained('nino_habitos')->cascadeOnDelete();
                $table->date('fecha');
                $table->boolean('completado')->default(false);
                $table->timestamp('registrado_en')->useCurrent();
                $table->unique(['nino_habito_id', 'fecha']);
                $table->index('fecha');
            });
        }

        if (! Schema::hasTable('retos_catalogo')) {
            Schema::create('retos_catalogo', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150);
                $table->text('descripcion')->nullable();
                $table->string('tipo', 20);
                $table->jsonb('condicion');
                $table->integer('puntos_recompensa')->default(0);
                $table->boolean('activo')->default(true);
                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('nino_retos')) {
            Schema::create('nino_retos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('nino_id')->constrained('ninos')->cascadeOnDelete();
                $table->foreignId('reto_id')->constrained('retos_catalogo')->cascadeOnDelete();
                $table->jsonb('progreso')->nullable();
                $table->boolean('completado')->default(false);
                $table->timestamp('completado_en')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('logros_catalogo')) {
            Schema::create('logros_catalogo', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150);
                $table->text('descripcion')->nullable();
                $table->string('icono', 100)->nullable();
                $table->jsonb('criterio')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('nino_logros')) {
            Schema::create('nino_logros', function (Blueprint $table) {
                $table->id();
                $table->foreignId('nino_id')->constrained('ninos')->cascadeOnDelete();
                $table->foreignId('logro_id')->constrained('logros_catalogo')->cascadeOnDelete();
                $table->timestamp('obtenido_en')->useCurrent();
                $table->unique(['nino_id', 'logro_id']);
            });
        }

        if (! Schema::hasTable('nino_puntos')) {
            Schema::create('nino_puntos', function (Blueprint $table) {
                $table->foreignId('nino_id')->primary()->constrained('ninos')->cascadeOnDelete();
                $table->integer('puntos_totales')->default(0);
                $table->integer('nivel_actual')->default(1);
                $table->timestamp('actualizado_en')->useCurrent();
            });
        }

        if (! Schema::hasTable('recompensas_catalogo')) {
            Schema::create('recompensas_catalogo', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150);
                $table->text('descripcion')->nullable();
                $table->integer('costo_puntos');
                $table->integer('stock')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('nino_recompensas')) {
            Schema::create('nino_recompensas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('nino_id')->constrained('ninos')->cascadeOnDelete();
                $table->foreignId('recompensa_id')->constrained('recompensas_catalogo')->cascadeOnDelete();
                $table->timestamp('canjeado_en')->useCurrent();
                $table->string('estado', 20)->default('pendiente');
            });
        }

        if (! Schema::hasTable('menu_items') && Schema::hasTable('menus')) {
            Schema::create('menu_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
                $table->string('dia_semana', 10);
                $table->string('tipo_comida', 20);
                $table->text('descripcion');
                $table->integer('calorias_aprox')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('nino_recompensas');
        Schema::dropIfExists('recompensas_catalogo');
        Schema::dropIfExists('nino_puntos');
        Schema::dropIfExists('nino_logros');
        Schema::dropIfExists('logros_catalogo');
        Schema::dropIfExists('nino_retos');
        Schema::dropIfExists('retos_catalogo');
        Schema::dropIfExists('habito_registros');
        Schema::dropIfExists('nino_habitos');
        Schema::dropIfExists('habitos_catalogo');
        Schema::dropIfExists('refresh_tokens');
    }
};
