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
        Schema::create('sesiones_punto_conexion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('punto_conexion_id')->constrained('puntos_conexion')->cascadeOnDelete();
            $table->date('fecha');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['punto_conexion_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesiones_punto_conexion');
    }
};
