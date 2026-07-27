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
        Schema::create('asistencias_punto_conexion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_punto_conexion_id')->constrained('sesiones_punto_conexion')->cascadeOnDelete();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->boolean('asistio')->default(false);
            $table->string('notas')->nullable();
            $table->timestamps();

            $table->unique(['sesion_punto_conexion_id', 'persona_id'], 'asistencias_pc_sesion_persona_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias_punto_conexion');
    }
};
