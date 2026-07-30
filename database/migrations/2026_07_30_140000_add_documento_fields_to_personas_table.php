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
            $table->enum('tipo_documento', [
                'cedula_ciudadania',
                'tarjeta_identidad',
                'registro_civil',
                'cedula_extranjeria',
                'permiso_proteccion_temporal',
                'permiso_especial_permanencia',
                'pasaporte',
                'otro',
            ])->nullable()->after('apellidos');
            $table->string('numero_documento')->nullable()->unique()->after('tipo_documento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropUnique(['numero_documento']);
            $table->dropColumn(['tipo_documento', 'numero_documento']);
        });
    }
};
