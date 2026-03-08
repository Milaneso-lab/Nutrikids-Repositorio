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
        Schema::table('Usuarios', function (Blueprint $table) {
            $table->string('rol', 20)->default('padre')->after('email');
            // Roles: 'admin', 'nutriologo', 'padre'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Usuarios', function (Blueprint $table) {
            $table->dropColumn('rol');
        });
    }
};
