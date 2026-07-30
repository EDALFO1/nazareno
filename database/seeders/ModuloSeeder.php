<?php

namespace Database\Seeders;

use App\Models\Modulo;
use Illuminate\Database\Seeder;

class ModuloSeeder extends Seeder
{
    /**
     * Catálogo de secciones del sistema. El acceso de cada rol a estos
     * módulos se define en RolSeeder (tabla rol_modulo) y se puede reasignar
     * después desde la pantalla de "Módulos por rol".
     */
    public function run(): void
    {
        $modulos = [
            ['slug' => 'personas', 'nombre' => 'Personas', 'grupo' => 'Personas y Redes', 'orden' => 1],
            ['slug' => 'redes', 'nombre' => 'Redes', 'grupo' => 'Personas y Redes', 'orden' => 2],
            ['slug' => 'puntos_conexion', 'nombre' => 'Puntos de conexión', 'grupo' => 'Personas y Redes', 'orden' => 3],
            ['slug' => 'estructura_red', 'nombre' => 'Estructura de red', 'grupo' => 'Personas y Redes', 'orden' => 4],
            ['slug' => 'codigo_qr_registro', 'nombre' => 'QR de registro', 'grupo' => 'Personas y Redes', 'orden' => 5],

            ['slug' => 'procesos', 'nombre' => 'Procesos de formación', 'grupo' => 'Procesos', 'orden' => 10],

            ['slug' => 'categorias_contables', 'nombre' => 'Categorías contables', 'grupo' => 'Finanzas', 'orden' => 20],
            ['slug' => 'cuentas_bancarias', 'nombre' => 'Cuentas bancarias', 'grupo' => 'Finanzas', 'orden' => 21],
            ['slug' => 'cuentas_pendientes', 'nombre' => 'Cuentas pendientes', 'grupo' => 'Finanzas', 'orden' => 22],
            ['slug' => 'movimientos_contables', 'nombre' => 'Movimientos contables', 'grupo' => 'Finanzas', 'orden' => 23],
            ['slug' => 'donaciones_activos', 'nombre' => 'Donaciones de activos', 'grupo' => 'Finanzas', 'orden' => 24],
            ['slug' => 'proveedores', 'nombre' => 'Proveedores', 'grupo' => 'Finanzas', 'orden' => 24],
            ['slug' => 'certificado_donante', 'nombre' => 'Certificado de donante', 'grupo' => 'Finanzas', 'orden' => 25],
            ['slug' => 'reportes', 'nombre' => 'Reporte financiero', 'grupo' => 'Finanzas', 'orden' => 26],

            ['slug' => 'usuarios', 'nombre' => 'Usuarios', 'grupo' => 'Sistema', 'orden' => 30],
            ['slug' => 'roles', 'nombre' => 'Roles', 'grupo' => 'Sistema', 'orden' => 31],
            ['slug' => 'modulos_rol', 'nombre' => 'Módulos por rol', 'grupo' => 'Sistema', 'orden' => 32],

            ['slug' => 'alertas', 'nombre' => 'Alertas', 'grupo' => null, 'orden' => 0],
        ];

        foreach ($modulos as $modulo) {
            Modulo::firstOrCreate(['slug' => $modulo['slug']], $modulo);
        }
    }
}
