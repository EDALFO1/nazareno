<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_contables', function (Blueprint $table) {
            $table->foreignId('proveedor_id')->nullable()->after('persona_id')->constrained('proveedores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_contables', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proveedor_id');
        });
    }
};
