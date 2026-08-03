<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            if (! Schema::hasColumn('pacientes', 'estado_paciente')) {
                $table->string('estado_paciente', 30)->default('activo')->after('fecha_nacimiento');
            }
            if (! Schema::hasColumn('pacientes', 'historia_clinica')) {
                $table->text('historia_clinica')->nullable()->after('estado_paciente');
            }
            if (! Schema::hasColumn('pacientes', 'antecedentes')) {
                $table->text('antecedentes')->nullable()->after('historia_clinica');
            }
            if (! Schema::hasColumn('pacientes', 'alergias')) {
                $table->text('alergias')->nullable()->after('antecedentes');
            }
            if (! Schema::hasColumn('pacientes', 'objetivo_nutricional')) {
                $table->text('objetivo_nutricional')->nullable()->after('alergias');
            }
            if (! Schema::hasColumn('pacientes', 'notas_seguimiento')) {
                $table->text('notas_seguimiento')->nullable()->after('objetivo_nutricional');
            }
        });

        Schema::table('menus', function (Blueprint $table) {
            if (! Schema::hasColumn('menus', 'estado')) {
                $table->string('estado', 20)->default('activo')->after('descripcion');
            }
            if (! Schema::hasColumn('menus', 'duplicado_de_id')) {
                $table->unsignedBigInteger('duplicado_de_id')->nullable()->after('estado');
            }
        });

        Schema::table('usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('usuarios', 'telefono')) {
                $table->string('telefono', 30)->nullable()->after('rol');
            }
            if (! Schema::hasColumn('usuarios', 'especialidad')) {
                $table->string('especialidad', 120)->nullable()->after('telefono');
            }
            if (! Schema::hasColumn('usuarios', 'disponibilidad')) {
                $table->string('disponibilidad', 255)->nullable()->after('especialidad');
            }
            if (! Schema::hasColumn('usuarios', 'foto_path')) {
                $table->string('foto_path', 255)->nullable()->after('disponibilidad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            foreach (['estado_paciente', 'historia_clinica', 'antecedentes', 'alergias', 'objetivo_nutricional', 'notas_seguimiento'] as $col) {
                if (Schema::hasColumn('pacientes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('menus', function (Blueprint $table) {
            foreach (['estado', 'duplicado_de_id'] as $col) {
                if (Schema::hasColumn('menus', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('usuarios', function (Blueprint $table) {
            foreach (['telefono', 'especialidad', 'disponibilidad', 'foto_path'] as $col) {
                if (Schema::hasColumn('usuarios', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
