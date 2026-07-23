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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->string('correo')->nullable();
            $table->enum('genero', ['masculino', 'femenino'])->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_primera_visita')->nullable();
            $table->text('peticion_oracion')->nullable();
            $table->enum('estado', ['nuevo', 'en_seguimiento', 'en_red', 'inactivo'])->default('nuevo');

            $table->foreignId('red_id')->nullable()->constrained('redes')->nullOnDelete();
            $table->foreignId('lider_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
