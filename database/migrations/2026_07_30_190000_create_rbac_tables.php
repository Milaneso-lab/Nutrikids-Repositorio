<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 50)->unique();
                $table->string('descripcion', 255)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permisos')) {
            Schema::create('permisos', function (Blueprint $table) {
                $table->id();
                $table->string('clave', 100)->unique();
                $table->string('descripcion', 255)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('rol_permiso')) {
            Schema::create('rol_permiso', function (Blueprint $table) {
                $table->unsignedBigInteger('rol_id');
                $table->unsignedBigInteger('permiso_id');
                $table->primary(['rol_id', 'permiso_id']);
            });
        }

        if (Schema::hasTable('usuarios') && ! Schema::hasColumn('usuarios', 'estado')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->string('estado', 30)->default('activo')->after('rol');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_permiso');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('roles');

        if (Schema::hasColumn('usuarios', 'estado')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropColumn('estado');
            });
        }
    }
};
