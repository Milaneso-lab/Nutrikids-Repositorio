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
        Schema::create('discusions', function (Blueprint $table) {
            $table->id('id_discusion');
            $table->string('tema', 255);
            $table->text('descripcion');
            $table->dateTime('fecha_creacion')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discusions');
    }
};
