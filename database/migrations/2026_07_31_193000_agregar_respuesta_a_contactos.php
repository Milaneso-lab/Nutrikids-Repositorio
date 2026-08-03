<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El panel permitía responder un mensaje de contacto, pero la respuesta sólo
 * devolvía «enviada exitosamente» sin guardarse en ningún sitio. Estas columnas
 * dan a la respuesta un lugar real en PostgreSQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contactos')) {
            return;
        }

        Schema::table('contactos', function (Blueprint $table) {
            if (! Schema::hasColumn('contactos', 'respuesta')) {
                $table->text('respuesta')->nullable();
            }
            if (! Schema::hasColumn('contactos', 'respondido_en')) {
                $table->timestamp('respondido_en')->nullable();
            }
            if (! Schema::hasColumn('contactos', 'respondido_por_id')) {
                $table->unsignedBigInteger('respondido_por_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('contactos')) {
            return;
        }

        Schema::table('contactos', function (Blueprint $table) {
            $table->dropColumn(['respuesta', 'respondido_en', 'respondido_por_id']);
        });
    }
};
