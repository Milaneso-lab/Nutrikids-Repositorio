<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La tabla `citas` existía solo con id y timestamps; vaciar antes de FK obligatorias
        DB::table('citas')->delete();

        Schema::table('citas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_padre')->after('id');
            $table->unsignedBigInteger('id_nutriologo')->nullable()->after('id_padre');
            $table->date('fecha_preferida')->after('id_nutriologo');
            $table->string('franja', 20)->default('manana')->after('fecha_preferida');
            $table->string('telefono', 30)->nullable()->after('franja');
            $table->text('mensaje')->nullable()->after('telefono');
            $table->string('estado', 20)->default('pendiente')->after('mensaje');

            $table->foreign('id_padre')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
            $table->foreign('id_nutriologo')->references('id_usuario')->on('usuarios')->nullOnDelete();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['id_padre']);
            $table->dropForeign(['id_nutriologo']);
            $table->dropColumn([
                'id_padre',
                'id_nutriologo',
                'fecha_preferida',
                'franja',
                'telefono',
                'mensaje',
                'estado',
            ]);
        });
    }
};
