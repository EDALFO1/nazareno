<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Crea los permisos que Filament Shield espera para cada resource protegido.
     * Se recrean aquí (en vez de depender únicamente de `shield:generate`, que solo
     * se ejecutó una vez de forma manual) para que una instalación nueva
     * (migrate:fresh --seed, o los tests) quede funcional sin pasos manuales.
     *
     * Si se agrega un Resource nuevo, correr:
     *   php artisan shield:generate --resource=NuevoResource --panel=admin
     * y añadir su slug aquí con el mismo prefijo usado por Shield.
     */
    public function run(): void
    {
        $prefijosResource = [
            'view', 'view_any', 'create', 'update',
            'restore', 'restore_any', 'replicate', 'reorder',
            'delete', 'delete_any', 'force_delete', 'force_delete_any',
        ];

        $resources = [
            'persona', 'punto::conexion', 'proceso', 'red', 'user',
            'categoria::contable', 'movimiento::contable', 'donacion::activo',
            'cuenta::bancaria', 'cuenta::pendiente',
        ];

        foreach ($resources as $resource) {
            foreach ($prefijosResource as $prefijo) {
                Permission::firstOrCreate(['name' => "{$prefijo}_{$resource}", 'guard_name' => 'web']);
            }
        }

        // Role (gestionado por Filament Shield) no expone restore/replicate/reorder/force_delete.
        foreach (['view', 'view_any', 'create', 'update', 'delete', 'delete_any'] as $prefijo) {
            Permission::firstOrCreate(['name' => "{$prefijo}_role", 'guard_name' => 'web']);
        }
    }
}
