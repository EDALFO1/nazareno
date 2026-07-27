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
        Schema::create('cuentas_pendientes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['por_cobrar', 'por_pagar']);
            $table->foreignId('categoria_contable_id')->nullable()->constrained('categorias_contables')->nullOnDelete();
            $table->foreignId('persona_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->string('descripcion');
            $table->decimal('monto_total', 12, 2);
            $table->date('fecha');
            $table->date('fecha_vencimiento')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas_pendientes');
    }
};
