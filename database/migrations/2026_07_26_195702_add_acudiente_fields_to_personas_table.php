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
        Schema::table('personas', function (Blueprint $table) {
            $table->string('acudiente')->nullable()->after('peticion_oracion');
            $table->string('telefono_acudiente')->nullable()->after('acudiente');
            $table->string('parentesco')->nullable()->after('telefono_acudiente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn(['acudiente', 'telefono_acudiente', 'parentesco']);
        });
    }
};
