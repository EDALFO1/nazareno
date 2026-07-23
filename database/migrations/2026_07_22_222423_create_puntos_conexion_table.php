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
        Schema::create('puntos_conexion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('red_id')->constrained('redes')->cascadeOnDelete();
            $table->foreignId('lider_id')->constrained('personas')->cascadeOnDelete();
            $table->foreignId('anfitrion_persona_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->enum('dia_semana', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'])->nullable();
            $table->time('hora')->nullable();
            $table->string('direccion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('punto_conexion_persona', function (Blueprint $table) {
            $table->id();
            $table->foreignId('punto_conexion_id')->constrained('puntos_conexion')->cascadeOnDelete();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->date('fecha_ingreso')->nullable();
            $table->timestamps();
            $table->unique(['punto_conexion_id', 'persona_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('punto_conexion_persona');
        Schema::dropIfExists('puntos_conexion');
    }
};
