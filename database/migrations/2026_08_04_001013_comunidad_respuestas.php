<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comentarios') && ! Schema::hasColumn('comentarios', 'id_comentario_padre')) {
            Schema::table('comentarios', function (Blueprint $table) {
                $table->unsignedBigInteger('id_comentario_padre')->nullable()->after('id_usuario');
                $table->foreign('id_comentario_padre')
                    ->references('id_comentario')
                    ->on('comentarios')
                    ->cascadeOnDelete();
                $table->index('id_comentario_padre');
            });
        }

        if (! Schema::hasTable('respuestas_discusion')) {
            Schema::create('respuestas_discusion', function (Blueprint $table) {
                $table->id('id_respuesta');
                $table->unsignedBigInteger('id_discusion');
                $table->unsignedBigInteger('id_usuario');
                $table->string('nombre', 50);
                $table->string('apellido', 50);
                $table->text('mensaje');
                $table->dateTime('fecha_creacion')->useCurrent();
                $table->foreign('id_discusion')->references('id_discusion')->on('discusiones')->cascadeOnDelete();
                $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
                $table->index('id_discusion');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('respuestas_discusion');

        if (Schema::hasTable('comentarios') && Schema::hasColumn('comentarios', 'id_comentario_padre')) {
            Schema::table('comentarios', function (Blueprint $table) {
                $table->dropForeign(['id_comentario_padre']);
                $table->dropColumn('id_comentario_padre');
            });
        }
    }
};
