<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('proceso_participantes', function (Blueprint $table) {
            $table->enum('estado_participacion', ['en_curso', 'terminado', 'retirado', 'incompleto'])
                ->default('en_curso')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('proceso_participantes')->where('estado_participacion', 'incompleto')->update(['estado_participacion' => 'retirado']);

        Schema::table('proceso_participantes', function (Blueprint $table) {
            $table->enum('estado_participacion', ['en_curso', 'terminado', 'retirado'])
                ->default('en_curso')
                ->change();
        });
    }
};
