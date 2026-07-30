<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reemplaza spatie/laravel-permission (roles + permissions granulares por
     * Resource) por el esquema Rol/Modulo de seguratech: un usuario tiene un
     * único rol, y cada rol tiene acceso a un conjunto de módulos (secciones
     * del sistema). El filtrado fino por fila (p. ej. qué personas ve un
     * líder de red) sigue resuelto por App\Services\AlcanceService, que no
     * depende de este esquema.
     */
    public function up(): void
    {
        // Captura las asignaciones actuales (spatie) antes de borrar sus tablas.
        $asignaciones = Schema::hasTable('model_has_roles')
            ? DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->pluck('roles.name', 'model_has_roles.model_id')
            : collect();

        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->string('grupo')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('rol_modulo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['rol_id', 'modulo_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('rol_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
        });

        $descripciones = [
            'super_admin' => 'Admin Principal — acceso total al sistema.',
            'admin_general' => 'Admin General — gestiona todos los módulos de datos, sin administrar usuarios ni roles.',
            'lider_red' => 'Líder de Red — solo ve su propio subárbol de discipulado.',
        ];

        $idsPorNombre = [];

        foreach ($descripciones as $nombre => $descripcion) {
            $idsPorNombre[$nombre] = DB::table('roles')->insertGetId([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($asignaciones as $userId => $nombreRol) {
            if (isset($idsPorNombre[$nombreRol])) {
                DB::table('users')->where('id', $userId)->update(['rol_id' => $idsPorNombre[$nombreRol]]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rol_id');
        });

        Schema::dropIfExists('rol_modulo');
        Schema::dropIfExists('modulos');
        Schema::dropIfExists('roles');
    }
};
