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
        Schema::table('movimientos_contables', function (Blueprint $table) {
            $table->foreignId('cuenta_bancaria_id')->nullable()->after('punto_conexion_id')->constrained('cuentas_bancarias')->nullOnDelete();
            $table->foreignId('cuenta_pendiente_id')->nullable()->after('cuenta_bancaria_id')->constrained('cuentas_pendientes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_contables', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cuenta_pendiente_id');
            $table->dropConstrainedForeignId('cuenta_bancaria_id');
        });
    }
};
