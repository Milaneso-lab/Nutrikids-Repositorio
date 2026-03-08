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
        if (Schema::hasTable('Usuarios') && !Schema::hasColumn('Usuarios', 'rol')) {
            Schema::table('Usuarios', function (Blueprint $table) {
                $table->string('rol', 20)->default('padre')->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('Usuarios', 'rol')) {
            Schema::table('Usuarios', function (Blueprint $table) {
                $table->dropColumn('rol');
            });
        }
    }
};
