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
        Schema::create('procesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_proceso_id')->constrained('tipos_proceso')->cascadeOnDelete();
            $table->string('nombre');
            $table->date('fecha_inicio')->nullable();
            $table->enum('estado', ['planificado', 'en_curso', 'finalizado'])->default('planificado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesos');
    }
};
