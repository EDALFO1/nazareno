<?php

namespace Database\Seeders;

use App\Models\Modulo;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Asigna a cada rol los módulos a los que tiene acceso. Los 3 roles
     * (super_admin, admin_general, lider_red) ya existen a esta altura:
     * los crea la migración 2026_07_30_150000_replace_permissions_with_roles_modulos
     * al migrar las asignaciones previas de spatie/laravel-permission.
     */
    public function run(): void
    {
        $todos = Modulo::pluck('slug')->all();

        $superAdmin = Rol::firstOrCreate(
            ['nombre' => 'super_admin'],
            ['descripcion' => 'Admin Principal — acceso total al sistema.']
        );
        $this->sincronizarModulos($superAdmin, $todos);

        $adminGeneral = Rol::firstOrCreate(
            ['nombre' => 'admin_general'],
            ['descripcion' => 'Admin General — gestiona todos los módulos de datos, sin administrar usuarios ni roles.']
        );
        $this->sincronizarModulos($adminGeneral, array_diff($todos, ['usuarios', 'roles', 'modulos_rol']));

        $liderRed = Rol::firstOrCreate(
            ['nombre' => 'lider_red'],
            ['descripcion' => 'Líder de Red — solo ve su propio subárbol de discipulado.']
        );
        $this->sincronizarModulos($liderRed, [
            'personas', 'puntos_conexion', 'estructura_red', 'codigo_qr_registro', 'alertas',
        ]);
    }

    /**
     * @param  array<int, string>  $slugs
     */
    private function sincronizarModulos(Rol $rol, array $slugs): void
    {
        $ids = Modulo::whereIn('slug', $slugs)->pluck('id');

        $rol->modulos()->sync($ids);
    }
}
