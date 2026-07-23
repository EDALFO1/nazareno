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
        Schema::create('proceso_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->foreignId('red_id')->nullable()->constrained('redes')->nullOnDelete();
            $table->enum('estado_participacion', ['en_curso', 'terminado', 'retirado'])->default('en_curso');
            $table->foreignId('sesion_retiro_id')->nullable()->constrained('sesiones_proceso')->nullOnDelete();
            $table->timestamps();
            $table->unique(['proceso_id', 'persona_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proceso_participantes');
    }
};
