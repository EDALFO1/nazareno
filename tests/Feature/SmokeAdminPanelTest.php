<?php

namespace Tests\Feature;

use App\Models\Persona;
use App\Models\Proceso;
use App\Models\PuntoConexion;
use App\Models\Red;
use App\Models\TipoProceso;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SmokeAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    public function test_pagina_publica_de_registro_carga_y_crea_persona_nueva(): void
    {
        $response = $this->get('/registro');
        $response->assertStatus(200);

        Livewire::test(\App\Livewire\RegistroPersonaNueva::class)
            ->set('nombres', 'Visitante')
            ->set('apellidos', 'De Prueba')
            ->set('telefono', '3000000000')
            ->set('correo', 'visitante@example.com')
            ->set('peticion_oracion', 'Por mi salud')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('personas', [
            'nombres' => 'Visitante',
            'apellidos' => 'De Prueba',
            'estado' => 'nuevo',
        ]);
    }

    public function test_admin_principal_puede_ver_todo_el_panel(): void
    {
        [$red, $carlos, $andres, , , $proceso] = $this->crearDatosDemo();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        $this->get('/admin')->assertStatus(200);
        $this->get('/admin/personas')->assertStatus(200);
        $this->get('/admin/puntos-conexion')->assertStatus(200);
        $this->get('/admin/procesos')->assertStatus(200);
        $this->get("/admin/procesos/{$proceso->id}/edit")->assertStatus(200);
        $this->get("/admin/personas/{$carlos->id}/edit")->assertStatus(200);
    }

    public function test_lider_de_red_solo_ve_su_propio_subarbol(): void
    {
        [$red, $carlos, $andres, $pedro, $marta] = $this->crearDatosDemo();

        $liderUser = User::factory()->create();
        $liderUser->assignRole('lider_red');
        $carlos->update(['user_id' => $liderUser->id]);

        $this->actingAs($liderUser);

        $visibles = \App\Filament\Resources\PersonaResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($carlos->id, $visibles);
        $this->assertContains($andres->id, $visibles);
        $this->assertContains($pedro->id, $visibles);
        $this->assertNotContains($marta->id, $visibles);

        $this->get('/admin/personas')->assertStatus(200);
    }

    /**
     * @return array{0: Red, 1: Persona, 2: Persona, 3: Persona, 4: Persona, 5: Proceso}
     */
    private function crearDatosDemo(): array
    {
        $redHombres = Red::create(['nombre' => 'Hombres']);
        Red::create(['nombre' => 'Mujeres']);
        $redMujeres = Red::where('nombre', 'Mujeres')->first();

        $carlos = Persona::create([
            'nombres' => 'Carlos', 'apellidos' => 'Ramírez', 'estado' => 'en_red', 'red_id' => $redHombres->id,
        ]);
        $andres = Persona::create([
            'nombres' => 'Andrés', 'apellidos' => 'Torres', 'estado' => 'en_red',
            'red_id' => $redHombres->id, 'lider_id' => $carlos->id,
        ]);
        $pedro = Persona::create([
            'nombres' => 'Pedro', 'apellidos' => 'Gómez', 'estado' => 'en_red',
            'red_id' => $redHombres->id, 'lider_id' => $carlos->id,
        ]);
        $marta = Persona::create([
            'nombres' => 'Marta', 'apellidos' => 'Suárez', 'estado' => 'en_red', 'red_id' => $redMujeres->id,
        ]);

        PuntoConexion::create([
            'nombre' => 'Punto de prueba',
            'red_id' => $redHombres->id,
            'lider_id' => $carlos->id,
            'dia_semana' => 'martes',
        ]);

        $tipoEncuentro = TipoProceso::create([
            'codigo' => 'encuentro', 'nombre' => 'Encuentro', 'numero_sesiones' => null, 'orden' => 1,
        ]);
        $proceso = Proceso::create([
            'tipo_proceso_id' => $tipoEncuentro->id,
            'nombre' => 'Encuentro de prueba',
            'estado' => 'en_curso',
        ]);
        $proceso->sesiones()->create(['numero_sesion' => 1]);
        $proceso->participantes()->create([
            'persona_id' => $andres->id, 'red_id' => $redHombres->id, 'estado_participacion' => 'en_curso',
        ]);

        return [$redHombres, $carlos, $andres, $pedro, $marta, $proceso];
    }
}
