<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('Discusiones', function (Blueprint $table) {
            $table->unsignedInteger('id_usuario')->nullable()->after('id_discusion');
            $table->foreign('id_usuario')->references('id_usuario')->on('Usuarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Discusiones', function (Blueprint $table) {
            $table->dropForeign(['id_usuario']);
            $table->dropColumn('id_usuario');
        });
    }
};
