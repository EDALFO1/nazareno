<?php

namespace Database\Seeders;

use App\Models\CategoriaContable;
use Illuminate\Database\Seeder;

class CategoriaContableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            ['tipo' => 'ingreso', 'nombre' => 'Diezmo'],
            ['tipo' => 'ingreso', 'nombre' => 'Ofrenda general'],
            ['tipo' => 'ingreso', 'nombre' => 'Ofrenda especial / siembra'],
            ['tipo' => 'ingreso', 'nombre' => 'Donación en efectivo'],
            ['tipo' => 'ingreso', 'nombre' => 'Donación en especie (activos)'],
            ['tipo' => 'ingreso', 'nombre' => 'Ingreso por evento'],
            ['tipo' => 'ingreso', 'nombre' => 'Otros ingresos'],

            ['tipo' => 'egreso', 'nombre' => 'Servicios públicos'],
            ['tipo' => 'egreso', 'nombre' => 'Compra de insumos'],
            ['tipo' => 'egreso', 'nombre' => 'Reparaciones y mantenimiento'],
            ['tipo' => 'egreso', 'nombre' => 'Honorarios / salario pastoral'],
            ['tipo' => 'egreso', 'nombre' => 'Donación realizada por la iglesia'],
            ['tipo' => 'egreso', 'nombre' => 'Compra de activos'],
            ['tipo' => 'egreso', 'nombre' => 'Arriendo'],
            ['tipo' => 'egreso', 'nombre' => 'Otros egresos'],
        ];

        foreach ($categorias as $categoria) {
            CategoriaContable::firstOrCreate(
                ['tipo' => $categoria['tipo'], 'nombre' => $categoria['nombre']]
            );
        }
    }
}
