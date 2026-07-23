<?php

namespace Database\Seeders;

use App\Models\Red;
use App\Models\TipoProceso;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);

        Red::firstOrCreate(['nombre' => 'Hombres']);
        Red::firstOrCreate(['nombre' => 'Mujeres']);

        $tiposProceso = [
            ['codigo' => 'encuentro', 'nombre' => 'Encuentro', 'numero_sesiones' => null, 'orden' => 1],
            ['codigo' => 'sanidad_integral', 'nombre' => 'Sanidad Integral', 'numero_sesiones' => 8, 'orden' => 2],
            ['codigo' => 'discipulado_m1', 'nombre' => 'Discipulado - Módulo 1', 'numero_sesiones' => 12, 'orden' => 3],
            ['codigo' => 'discipulado_m2', 'nombre' => 'Discipulado - Módulo 2', 'numero_sesiones' => 12, 'orden' => 4],
            ['codigo' => 'discipulado_m3', 'nombre' => 'Discipulado - Módulo 3', 'numero_sesiones' => 12, 'orden' => 5],
        ];

        foreach ($tiposProceso as $tipo) {
            TipoProceso::firstOrCreate(['codigo' => $tipo['codigo']], $tipo);
        }

        $adminPrincipal = User::firstOrCreate(
            ['email' => 'admin@ciudaddedios.test'],
            ['name' => 'Admin Principal', 'password' => bcrypt('CiudadDeDios2026!')]
        );
        $adminPrincipal->assignRole('super_admin');
    }
}
