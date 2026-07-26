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
        Schema::create('movimientos_contables', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->foreignId('categoria_contable_id')->constrained('categorias_contables')->restrictOnDelete();
            $table->date('fecha');
            $table->decimal('monto', 12, 2);
            $table->enum('metodo_pago', ['efectivo', 'consignacion', 'transferencia', 'cheque'])->default('efectivo');
            $table->foreignId('persona_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->foreignId('red_id')->nullable()->constrained('redes')->nullOnDelete();
            $table->foreignId('punto_conexion_id')->nullable()->constrained('puntos_conexion')->nullOnDelete();
            $table->string('descripcion')->nullable();
            $table->string('referencia')->nullable();
            $table->string('comprobante')->nullable();
            $table->foreignId('registrado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tipo', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_contables');
    }
};
