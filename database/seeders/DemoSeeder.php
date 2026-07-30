<?php

namespace Database\Seeders;

use App\Models\Asistencia;
use App\Models\NotaSeguimiento;
use App\Models\Persona;
use App\Models\Proceso;
use App\Models\PuntoConexion;
use App\Models\Red;
use App\Models\Rol;
use App\Models\TipoProceso;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Datos de ejemplo para verificar el sistema de punta a punta.
     */
    public function run(): void
    {
        $redHombres = Red::where('nombre', 'Hombres')->firstOrFail();
        $redMujeres = Red::where('nombre', 'Mujeres')->firstOrFail();

        // --- Jerarquía red Hombres (3 niveles) ---
        $carlos = Persona::create([
            'nombres' => 'Carlos', 'apellidos' => 'Ramírez',
            'telefono' => '3001111111', 'estado' => 'en_red', 'red_id' => $redHombres->id,
        ]);
        $carlosUser = User::firstOrCreate(
            ['email' => 'carlos.lider@ciudaddedios.test'],
            [
                'name' => 'Carlos Ramírez',
                'password' => bcrypt('Lider2026!'),
                'rol_id' => Rol::where('nombre', 'lider_red')->value('id'),
            ]
        );
        $carlos->update(['user_id' => $carlosUser->id]);

        $andres = Persona::create([
            'nombres' => 'Andrés', 'apellidos' => 'Torres',
            'telefono' => '3002222222', 'estado' => 'en_red', 'red_id' => $redHombres->id, 'lider_id' => $carlos->id,
        ]);
        $pedro = Persona::create([
            'nombres' => 'Pedro', 'apellidos' => 'Gómez',
            'telefono' => '3003333333', 'estado' => 'en_red', 'red_id' => $redHombres->id, 'lider_id' => $carlos->id,
        ]);
        $miguel = Persona::create([
            'nombres' => 'Miguel', 'apellidos' => 'Nieto',
            'telefono' => '3004444444', 'estado' => 'en_red', 'red_id' => $redHombres->id, 'lider_id' => $andres->id,
        ]);

        // --- Jerarquía red Mujeres ---
        $marta = Persona::create([
            'nombres' => 'Marta', 'apellidos' => 'Suárez',
            'telefono' => '3005555555', 'estado' => 'en_red', 'red_id' => $redMujeres->id,
        ]);
        $lucia = Persona::create([
            'nombres' => 'Lucía', 'apellidos' => 'Padilla',
            'telefono' => '3006666666', 'estado' => 'en_red', 'red_id' => $redMujeres->id, 'lider_id' => $marta->id,
        ]);

        // --- Personas nuevas sin asignar aún ---
        $visitante1 = Persona::create([
            'nombres' => 'Julián', 'apellidos' => 'Restrepo',
            'telefono' => '3007777777', 'estado' => 'nuevo',
            'fecha_primera_visita' => now()->subDays(5)->toDateString(),
            'peticion_oracion' => 'Por su nuevo trabajo',
        ]);
        NotaSeguimiento::create([
            'persona_id' => $visitante1->id,
            'fecha' => now()->subDays(2)->toDateString(),
            'nota' => 'Se le llamó, indicó que quiere volver el próximo domingo.',
        ]);
        Persona::create([
            'nombres' => 'Camila', 'apellidos' => 'Vargas',
            'telefono' => '3008888888', 'estado' => 'nuevo',
            'fecha_primera_visita' => now()->subDays(1)->toDateString(),
        ]);

        // --- Punto de conexión ---
        $punto = PuntoConexion::create([
            'nombre' => 'Punto Carlos - Martes',
            'red_id' => $redHombres->id,
            'lider_id' => $carlos->id,
            'anfitrion_persona_id' => $andres->id,
            'dia_semana' => 'martes',
            'hora' => '19:00',
            'direccion' => 'Cra 10 # 20-30',
        ]);
        $punto->miembros()->attach([$andres->id, $pedro->id], ['fecha_ingreso' => now()->toDateString()]);

        // --- Encuentro (edición con participantes de ambas redes) ---
        $tipoEncuentro = TipoProceso::where('codigo', 'encuentro')->firstOrFail();
        $encuentro = Proceso::create([
            'tipo_proceso_id' => $tipoEncuentro->id,
            'nombre' => 'Encuentro Julio 2026',
            'fecha_inicio' => now()->subWeeks(3)->toDateString(),
            'estado' => 'finalizado',
        ]);
        $sesionesEncuentro = collect(range(1, 3))->map(fn ($n) => $encuentro->sesiones()->create([
            'numero_sesion' => $n,
            'fecha' => now()->subWeeks(3)->addDays(($n - 1) * 1),
        ]));

        $encuentro->participantes()->create([
            'persona_id' => $andres->id, 'red_id' => $redHombres->id, 'estado_participacion' => 'terminado',
        ]);
        $encuentro->participantes()->create([
            'persona_id' => $pedro->id, 'red_id' => $redHombres->id, 'estado_participacion' => 'retirado',
            'sesion_retiro_id' => $sesionesEncuentro[2]->id,
        ]);
        $encuentro->participantes()->create([
            'persona_id' => $lucia->id, 'red_id' => $redMujeres->id, 'estado_participacion' => 'terminado',
        ]);

        foreach ($sesionesEncuentro as $i => $sesion) {
            Asistencia::create(['sesion_proceso_id' => $sesion->id, 'persona_id' => $andres->id, 'asistio' => true]);
            Asistencia::create(['sesion_proceso_id' => $sesion->id, 'persona_id' => $lucia->id, 'asistio' => true]);
            Asistencia::create(['sesion_proceso_id' => $sesion->id, 'persona_id' => $pedro->id, 'asistio' => $i < 2]);
        }

        // --- Sanidad Integral (edición en curso, Andrés avanzando) ---
        $tipoSanidad = TipoProceso::where('codigo', 'sanidad_integral')->firstOrFail();
        $sanidad = Proceso::create([
            'tipo_proceso_id' => $tipoSanidad->id,
            'nombre' => 'Sanidad Integral Julio 2026',
            'fecha_inicio' => now()->subWeeks(1)->toDateString(),
            'estado' => 'en_curso',
        ]);
        $sesionesSanidad = collect(range(1, 8))->map(fn ($n) => $sanidad->sesiones()->create(['numero_sesion' => $n]));

        $sanidad->participantes()->create([
            'persona_id' => $andres->id, 'red_id' => $redHombres->id, 'estado_participacion' => 'en_curso',
        ]);

        foreach ($sesionesSanidad->take(3) as $sesion) {
            Asistencia::create(['sesion_proceso_id' => $sesion->id, 'persona_id' => $andres->id, 'asistio' => true]);
        }

        // --- Admin General de prueba ---
        User::firstOrCreate(
            ['email' => 'admin.general@ciudaddedios.test'],
            [
                'name' => 'Admin General',
                'password' => bcrypt('AdminGeneral2026!'),
                'rol_id' => Rol::where('nombre', 'admin_general')->value('id'),
            ]
        );
    }
}
