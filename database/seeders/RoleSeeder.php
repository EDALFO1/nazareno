<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // super_admin = Admin Principal: con `define_via_gate` en false, Filament
        // Shield no usa un bypass de Gate, así que el rol debe tener explícitamente
        // TODOS los permisos (incluida la gestión de usuarios/roles).
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::where('guard_name', 'web')->get());

        // admin_general: todos los permisos de los módulos de datos, pero sin
        // gestionar roles/usuarios (eso es exclusivo del Admin Principal).
        $adminGeneral = Role::firstOrCreate(['name' => 'admin_general', 'guard_name' => 'web']);
        $adminGeneral->syncPermissions(
            Permission::where('guard_name', 'web')
                ->where('name', 'not like', '%role%')
                ->where('name', 'not like', '%user%')
                ->get()
        );

        // lider_red: puede ver, crear y actualizar personas (scoping a su propio
        // subárbol se aplica en PersonaPolicy / PersonaResource::getEloquentQuery()).
        $liderRed = Role::firstOrCreate(['name' => 'lider_red', 'guard_name' => 'web']);
        $liderRed->syncPermissions(
            Permission::where('guard_name', 'web')
                ->whereIn('name', [
                    'view_any_persona',
                    'view_persona',
                    'create_persona',
                    'update_persona',
                    'view_any_punto::conexion',
                    'view_punto::conexion',
                    'create_punto::conexion',
                    'update_punto::conexion',
                ])
                ->get()
        );
    }
}
