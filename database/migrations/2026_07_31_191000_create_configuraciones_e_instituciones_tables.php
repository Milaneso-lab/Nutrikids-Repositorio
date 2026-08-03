<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cierra las dos fuentes de datos que vivían fuera de PostgreSQL:
 * la configuración del sistema (que se descartaba en memoria y devolvía éxito)
 * y el catálogo de instituciones (que se guardaba en storage/app/instituciones.json).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('configuraciones')) {
            Schema::create('configuraciones', function (Blueprint $table) {
                $table->string('clave', 100)->primary();
                $table->text('valor')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('instituciones')) {
            Schema::create('instituciones', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150);
                $table->string('tipo', 20);
                $table->string('ciudad', 100)->nullable();
                $table->string('contacto_email', 120)->nullable();
                $table->boolean('activa')->default(true);
                $table->timestamps();

                $table->index('activa');
                $table->index('tipo');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('instituciones');
        Schema::dropIfExists('configuraciones');
    }
};
