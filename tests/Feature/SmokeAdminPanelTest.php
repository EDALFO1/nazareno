<?php

namespace Tests\Feature;

use App\Models\Persona;
use App\Models\Proceso;
use App\Models\PuntoConexion;
use App\Models\Red;
use App\Models\TipoProceso;
use App\Models\User;
use Database\Seeders\CategoriaContableSeeder;
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
        $this->seed(CategoriaContableSeeder::class);
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

    public function test_control_de_sesion_por_pestana_marca_login_y_cierra_sesion_en_pestana_nueva(): void
    {
        // En la página de login (sin sesión), el script marca la pestaña.
        $paginaLogin = $this->get('/admin/login');
        $paginaLogin->assertStatus(200);
        $paginaLogin->assertSee("sessionStorage.setItem('ccd_sesion_pestana', '1')", false);

        // Ya autenticado, el script revisa la marca de la pestaña y, si falta
        // (pestaña nueva heredando una cookie vieja), fuerza el logout.
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $paginaAutenticada = $this->get('/admin');
        $paginaAutenticada->assertStatus(200);
        $paginaAutenticada->assertSee('sesion-expirada', false);

        // La ruta que dispara ese script sí invalida la sesión de verdad.
        $this->get('/admin/sesion-expirada')->assertRedirect('/admin/login');
        $this->assertGuest();
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
        $this->get('/admin/redes')->assertStatus(200);
        $this->get('/admin/estructura-red')->assertStatus(200);
        $this->get('/admin/users')->assertStatus(200);
        $this->get('/admin/categorias-contables')->assertStatus(200);
        $this->get('/admin/movimientos-contables')->assertStatus(200);
        $this->get('/admin/donaciones-activos')->assertStatus(200);
        $this->get('/admin/certificado-donante')->assertStatus(200);
        $this->get('/admin/reporte-financiero')->assertStatus(200);
        $this->get("/admin/procesos/{$proceso->id}/edit")->assertStatus(200);
        $this->get("/admin/personas/{$carlos->id}/edit")->assertStatus(200);
        $this->get("/admin/redes/{$red->id}/edit")->assertStatus(200);
    }

    public function test_registrar_ingreso_de_diezmo_y_generar_certificado_de_donante(): void
    {
        [, $carlos] = $this->crearDatosDemo();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $categoriaDiezmo = \App\Models\CategoriaContable::where('tipo', 'ingreso')
            ->where('nombre', 'Diezmo')
            ->firstOrFail();

        Livewire::test(\App\Filament\Resources\MovimientoContableResource\Pages\CreateMovimientoContable::class)
            ->fillForm([
                'tipo' => 'ingreso',
                'categoria_contable_id' => $categoriaDiezmo->id,
                'fecha' => now()->format('Y-m-d'),
                'monto' => 150000,
                'metodo_pago' => 'consignacion',
                'referencia' => 'CONS-001',
                'persona_id' => $carlos->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('movimientos_contables', [
            'persona_id' => $carlos->id,
            'tipo' => 'ingreso',
            'monto' => 150000.00,
            'registrado_por_id' => $admin->id,
        ]);

        $certificado = Livewire::test(\App\Filament\Pages\CertificadoDonante::class)
            ->set('personaId', $carlos->id)
            ->set('anio', (int) now()->year)
            ->assertOk()
            ->assertSee('Carlos Ramírez')
            ->instance()
            ->certificado();

        $this->assertSame(150000.0, $certificado['totalEfectivo']);
    }

    public function test_reporte_financiero_calcula_saldo_ingresos_y_egresos(): void
    {
        [, $carlos] = $this->crearDatosDemo();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $categoriaDiezmo = \App\Models\CategoriaContable::where('tipo', 'ingreso')->where('nombre', 'Diezmo')->firstOrFail();
        $categoriaServicios = \App\Models\CategoriaContable::where('tipo', 'egreso')->where('nombre', 'Servicios públicos')->firstOrFail();

        \App\Models\MovimientoContable::create([
            'tipo' => 'ingreso',
            'categoria_contable_id' => $categoriaDiezmo->id,
            'fecha' => now(),
            'monto' => 200000,
            'metodo_pago' => 'efectivo',
            'persona_id' => $carlos->id,
        ]);

        \App\Models\MovimientoContable::create([
            'tipo' => 'egreso',
            'categoria_contable_id' => $categoriaServicios->id,
            'fecha' => now(),
            'monto' => 80000,
            'metodo_pago' => 'transferencia',
            'descripcion' => 'Recibo de energía',
        ]);

        $reporte = Livewire::test(\App\Filament\Pages\ReporteFinanciero::class)
            ->assertOk()
            ->instance()
            ->reporte();

        $this->assertSame(120000.0, $reporte['saldoActual']);
        $this->assertSame(200000.0, $reporte['totalIngresos']);
        $this->assertSame(80000.0, $reporte['totalEgresos']);
        $this->assertSame('Diezmo', $reporte['ingresosPorCategoria']->first()['categoria']);
        $this->assertSame('Servicios públicos', $reporte['egresosPorCategoria']->first()['categoria']);
    }

    public function test_lider_de_red_no_puede_ver_finanzas(): void
    {
        $liderUser = User::factory()->create();
        $liderUser->assignRole('lider_red');
        $this->actingAs($liderUser);

        $this->get('/admin/movimientos-contables')->assertForbidden();
        $this->get('/admin/categorias-contables')->assertForbidden();
        $this->get('/admin/donaciones-activos')->assertForbidden();
        $this->get('/admin/reporte-financiero')->assertForbidden();
    }

    public function test_crear_usuario_con_rol_lider_de_red_y_vincularlo_a_una_persona(): void
    {
        [$red, $carlos] = $this->crearDatosDemo();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\UserResource\Pages\CreateUser::class)
            ->fillForm([
                'name' => 'Carlos Ramírez',
                'email' => 'carlos.lider@ciudaddedios.test',
                'password' => 'Lider2026!',
                'roles' => [\Spatie\Permission\Models\Role::where('name', 'lider_red')->first()->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $nuevoUsuario = User::where('email', 'carlos.lider@ciudaddedios.test')->firstOrFail();

        $this->assertTrue($nuevoUsuario->hasRole('lider_red'));
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('Lider2026!', $nuevoUsuario->password));

        $carlos->update(['user_id' => $nuevoUsuario->id]);
        $this->assertSame($carlos->id, $nuevoUsuario->fresh()->persona->id);
    }

    public function test_lineas_de_liderazgo_solo_marcan_a_quienes_lideran_a_alguien(): void
    {
        $red = Red::create(['nombre' => 'Hombres']);

        $victor = Persona::create([
            'nombres' => 'Victor', 'apellidos' => 'Velez', 'estado' => 'en_red', 'red_id' => $red->id,
        ]);
        $gabriel = Persona::create([
            'nombres' => 'Gabriel', 'apellidos' => 'Santacruz', 'estado' => 'en_red',
            'red_id' => $red->id, 'lider_id' => $victor->id,
        ]);
        $polanco = Persona::create([
            'nombres' => 'Victor', 'apellidos' => 'Polanco', 'estado' => 'en_seguimiento',
            'red_id' => $red->id, 'lider_id' => $gabriel->id,
        ]);

        // Victor: líder principal — su propia marca (estrella), sin etiqueta de línea.
        $this->assertTrue($victor->fresh()->es_lider_principal);
        $this->assertNull($victor->fresh()->etiqueta_linea);

        // Gabriel: lidera a Polanco, está a un paso del principal -> 1ª línea.
        $this->assertSame(1, $gabriel->fresh()->linea_liderazgo);
        $this->assertSame('1ª línea', $gabriel->fresh()->etiqueta_linea);

        // Polanco: no lidera a nadie -> sin marca, aunque esté "más abajo" en el árbol.
        $this->assertNull($polanco->fresh()->linea_liderazgo);
        $this->assertNull($polanco->fresh()->etiqueta_linea);

        // Si Polanco empieza a liderar a alguien, pasa a ser 2ª línea.
        Persona::create([
            'nombres' => 'Nuevo', 'apellidos' => 'Discípulo', 'estado' => 'nuevo',
            'red_id' => $red->id, 'lider_id' => $polanco->id,
        ]);

        $this->assertSame(2, $polanco->fresh()->linea_liderazgo);
        $this->assertSame('2ª línea', $polanco->fresh()->etiqueta_linea);
    }

    public function test_lider_de_red_no_puede_gestionar_usuarios(): void
    {
        $liderUser = User::factory()->create();
        $liderUser->assignRole('lider_red');
        $this->actingAs($liderUser);

        $this->get('/admin/users')->assertForbidden();
    }

    public function test_estructura_de_red_muestra_la_rama_completa_del_lider_principal(): void
    {
        [$red, $carlos, $andres, $pedro, $marta] = $this->crearDatosDemo();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $componente = Livewire::test(\App\Filament\Pages\EstructuraRed::class)
            ->set('liderId', $carlos->id)
            ->assertOk()
            ->assertSee('Andrés Torres')
            ->assertSee('Pedro Gómez')
            ->assertSee('Punto de prueba')
            ->instance();

        $estructura = $componente->estructura();
        $idsEnRama = collect($estructura['arbol']['hijos'])->pluck('persona.id');

        $this->assertSame(3, $estructura['resumen']['personas']);
        $this->assertSame(1, $estructura['resumen']['lideres']);
        $this->assertSame(1, $estructura['resumen']['puntos']);
        $this->assertNotContains($marta->id, $idsEnRama);
    }

    public function test_personas_de_red_navega_por_niveles(): void
    {
        [$red, $carlos, $andres, $pedro, $marta] = $this->crearDatosDemo();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $manager = Livewire::test(\App\Filament\Resources\RedResource\RelationManagers\PersonasRelationManager::class, [
            'ownerRecord' => $red,
            'pageClass' => \App\Filament\Resources\RedResource\Pages\EditRed::class,
        ]);

        // Nivel raíz: solo el líder principal de la red.
        $manager->assertSee('Carlos Ramírez')
            ->assertDontSee('Andrés Torres')
            ->assertDontSee('Pedro Gómez');

        // Al entrar a su rama, se ven sus discípulos directos.
        $manager->callTableAction('ver_rama', $carlos)
            ->assertSee('Andrés Torres')
            ->assertSee('Pedro Gómez');

        // Volver a la raíz restaura la vista de líderes principales.
        $manager->callTableAction('subir_nivel')
            ->assertSee('Carlos Ramírez')
            ->assertDontSee('Andrés Torres');
    }

    public function test_crear_proceso_carga_automaticamente_a_quienes_terminaron_el_anterior(): void
    {
        [$red, $carlos, $andres, $pedro, $marta, $encuentro] = $this->crearDatosDemo();

        // Andrés terminó el Encuentro; Pedro sigue en curso (no terminó).
        $encuentro->participantes()->where('persona_id', $andres->id)->update(['estado_participacion' => 'terminado']);
        $encuentro->participantes()->create([
            'persona_id' => $pedro->id, 'red_id' => $red->id, 'estado_participacion' => 'en_curso',
        ]);

        $tipoSanidad = TipoProceso::create([
            'codigo' => 'sanidad_integral', 'nombre' => 'Sanidad Integral', 'numero_sesiones' => null, 'orden' => 2,
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\ProcesoResource\Pages\CreateProceso::class)
            ->fillForm([
                'tipo_proceso_id' => $tipoSanidad->id,
                'nombre' => 'Sanidad Integral de prueba',
                'estado' => 'planificado',
                'cargar_desde_proceso_id' => $encuentro->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $nuevoProceso = Proceso::where('nombre', 'Sanidad Integral de prueba')->firstOrFail();

        $this->assertSame(1, $nuevoProceso->participantes()->count());
        $this->assertTrue($nuevoProceso->participantes()->where('persona_id', $andres->id)->exists());
        $this->assertFalse($nuevoProceso->participantes()->where('persona_id', $pedro->id)->exists());
        $this->assertSame('en_curso', $nuevoProceso->participantes()->first()->estado_participacion);
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

    public function test_campo_usuario_del_sistema_solo_lo_ve_admin_no_lider_de_red(): void
    {
        $liderUser = User::factory()->create();
        $liderUser->assignRole('lider_red');
        $this->actingAs($liderUser);

        Livewire::test(\App\Filament\Resources\PersonaResource\Pages\CreatePersona::class)
            ->assertFormFieldIsHidden('user_id');

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\PersonaResource\Pages\CreatePersona::class)
            ->assertFormFieldIsVisible('user_id');
    }

    public function test_lider_de_red_ve_su_propia_rama_en_estructura_de_red_sin_seleccionar(): void
    {
        [$red, $carlos, $andres, $pedro, $marta] = $this->crearDatosDemo();

        $liderUser = User::factory()->create();
        $liderUser->assignRole('lider_red');
        $carlos->update(['user_id' => $liderUser->id]);

        $this->actingAs($liderUser);

        $test = Livewire::test(\App\Filament\Pages\EstructuraRed::class)
            ->assertOk()
            ->assertSee('Andrés Torres')
            ->assertSee('Pedro Gómez');

        // Se auto-selecciona a sí mismo, sin necesidad de elegirlo en un desplegable.
        $this->assertSame($carlos->id, $test->instance()->liderId);

        // Intentar ver la rama de Marta (fuera de su alcance) por la URL no funciona.
        $test->set('liderId', $marta->id);
        $this->assertNull($test->instance()->estructura());
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
