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
            ->set('genero', 'femenino')
            ->set('fecha_nacimiento', '1990-05-20')
            ->set('peticion_oracion', 'Por mi salud')
            ->set('autorizoDatos', true)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('personas', [
            'nombres' => 'Visitante',
            'apellidos' => 'De Prueba',
            'estado' => 'nuevo',
            'genero' => 'femenino',
        ]);

        $persona = Persona::where('nombres', 'Visitante')->firstOrFail();
        $this->assertSame('1990-05-20', $persona->fecha_nacimiento->toDateString());
        $this->assertDatabaseHas('autorizaciones_tratamiento_datos', [
            'persona_id' => $persona->id,
            'canal' => 'formulario_publico',
        ]);
    }

    public function test_registro_publico_de_un_menor_de_edad_guarda_los_datos_del_acudiente(): void
    {
        Livewire::test(\App\Livewire\RegistroPersonaNueva::class)
            ->set('nombres', 'Niño')
            ->set('apellidos', 'DePrueba')
            ->set('acudiente', 'Madre De Prueba')
            ->set('telefono_acudiente', '3000000001')
            ->set('parentesco', 'madre')
            ->set('autorizoDatos', true)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('personas', [
            'nombres' => 'Niño',
            'apellidos' => 'DePrueba',
            'acudiente' => 'Madre De Prueba',
            'telefono_acudiente' => '3000000001',
            'parentesco' => 'madre',
        ]);
    }

    public function test_registro_publico_sin_elegir_genero_ni_parentesco_no_falla_por_select_vacio(): void
    {
        // Un <select> sin opción elegida manda '' (no null); si no se normaliza
        // antes de validar, la regla in:masculino,femenino la rechaza.
        Livewire::test(\App\Livewire\RegistroPersonaNueva::class)
            ->set('nombres', 'Sin')
            ->set('apellidos', 'Genero')
            ->set('genero', '')
            ->set('parentesco', '')
            ->set('autorizoDatos', true)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('personas', ['nombres' => 'Sin', 'apellidos' => 'Genero']);
    }

    public function test_registro_publico_bloquea_despues_de_demasiados_envios_desde_la_misma_ip(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Livewire::test(\App\Livewire\RegistroPersonaNueva::class)
                ->set('nombres', "Visitante{$i}")
                ->set('apellidos', 'Prueba')
                ->set('autorizoDatos', true)
                ->call('guardar')
                ->assertHasNoErrors();
        }

        $this->assertSame(20, Persona::where('apellidos', 'Prueba')->count());

        Livewire::test(\App\Livewire\RegistroPersonaNueva::class)
            ->set('nombres', 'Visitante21')
            ->set('apellidos', 'Prueba')
            ->set('autorizoDatos', true)
            ->call('guardar')
            ->assertHasErrors(['nombres']);

        $this->assertSame(20, Persona::where('apellidos', 'Prueba')->count());
    }

    public function test_registro_publico_exige_marcar_la_autorizacion_de_datos(): void
    {
        Livewire::test(\App\Livewire\RegistroPersonaNueva::class)
            ->set('nombres', 'Sin')
            ->set('apellidos', 'Autorizar')
            ->call('guardar')
            ->assertHasErrors(['autorizoDatos' => 'accepted']);

        $this->assertDatabaseMissing('personas', ['nombres' => 'Sin', 'apellidos' => 'Autorizar']);
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
        $this->get('/admin/cuentas-bancarias')->assertStatus(200);
        $this->get('/admin/cuentas-pendientes')->assertStatus(200);
        $this->get("/admin/procesos/{$proceso->id}/edit")->assertStatus(200);
        $this->get("/admin/personas/{$carlos->id}/edit")->assertStatus(200);
        $this->get("/admin/redes/{$red->id}/edit")->assertStatus(200);
    }

    public function test_pagina_de_codigo_qr_de_registro_muestra_el_qr_y_el_enlace(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        Livewire::test(\App\Filament\Pages\CodigoQrRegistro::class)
            ->assertOk()
            ->assertSee(url('/registro'))
            ->assertSeeHtml('<svg');
    }

    public function test_comprobante_de_movimiento_contable_usa_disco_privado_no_publico(): void
    {
        // Regresión: los comprobantes (recibos/facturas) son documentos
        // financieros. Si el campo usara el disco "public" por defecto de
        // Filament, cualquiera con el enlace (aunque sea aleatorio) podría
        // verlos sin iniciar sesión. Deben quedar en el disco "local"
        // (storage/app/private) con visibilidad "private", para que Filament
        // genere enlaces firmados y temporales en vez de una URL pública fija.
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $campo = Livewire::test(\App\Filament\Resources\MovimientoContableResource\Pages\CreateMovimientoContable::class)
            ->instance()
            ->form
            ->getFlatFields()['comprobante'];

        $this->assertSame('local', $campo->getDiskName());
        $this->assertSame('private', $campo->getVisibility());
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

    public function test_cuenta_bancaria_calcula_saldo_actual_a_partir_de_sus_movimientos(): void
    {
        [, $carlos] = $this->crearDatosDemo();

        $categoriaDiezmo = \App\Models\CategoriaContable::where('tipo', 'ingreso')->where('nombre', 'Diezmo')->firstOrFail();
        $categoriaServicios = \App\Models\CategoriaContable::where('tipo', 'egreso')->where('nombre', 'Servicios públicos')->firstOrFail();

        $cuenta = \App\Models\CuentaBancaria::create([
            'nombre' => 'Cuenta de Ahorros Bancolombia',
            'banco' => 'Bancolombia',
            'tipo_cuenta' => 'ahorros',
            'saldo_inicial' => 500000,
        ]);

        \App\Models\MovimientoContable::create([
            'tipo' => 'ingreso',
            'categoria_contable_id' => $categoriaDiezmo->id,
            'fecha' => now(),
            'monto' => 200000,
            'metodo_pago' => 'consignacion',
            'persona_id' => $carlos->id,
            'cuenta_bancaria_id' => $cuenta->id,
        ]);

        \App\Models\MovimientoContable::create([
            'tipo' => 'egreso',
            'categoria_contable_id' => $categoriaServicios->id,
            'fecha' => now(),
            'monto' => 80000,
            'metodo_pago' => 'transferencia',
            'cuenta_bancaria_id' => $cuenta->id,
        ]);

        // Un movimiento en efectivo sin cuenta bancaria no debe afectar este saldo.
        \App\Models\MovimientoContable::create([
            'tipo' => 'ingreso',
            'categoria_contable_id' => $categoriaDiezmo->id,
            'fecha' => now(),
            'monto' => 999999,
            'metodo_pago' => 'efectivo',
        ]);

        $this->assertSame(620000.0, (float) $cuenta->fresh()->saldo_actual);
    }

    public function test_cuenta_pendiente_por_pagar_cambia_de_estado_al_registrar_abonos(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $categoriaReparaciones = \App\Models\CategoriaContable::where('tipo', 'egreso')
            ->where('nombre', 'Reparaciones y mantenimiento')
            ->firstOrFail();

        $cuenta = \App\Models\CuentaPendiente::create([
            'tipo' => 'por_pagar',
            'categoria_contable_id' => $categoriaReparaciones->id,
            'descripcion' => 'Reparación del techo - Ferretería XYZ',
            'monto_total' => 500000,
            'fecha' => now(),
        ]);

        $this->assertSame('pendiente', $cuenta->fresh()->estado);
        $this->assertSame(500000.0, $cuenta->fresh()->saldo_pendiente);

        // Primer abono parcial, registrado desde el relation manager de la cuenta.
        Livewire::test(\App\Filament\Resources\CuentaPendienteResource\RelationManagers\MovimientosRelationManager::class, [
            'ownerRecord' => $cuenta,
            'pageClass' => \App\Filament\Resources\CuentaPendienteResource\Pages\EditCuentaPendiente::class,
        ])
            ->callTableAction('create', data: [
                'fecha' => now()->format('Y-m-d'),
                'monto' => 200000,
                'metodo_pago' => 'efectivo',
            ]);

        $this->assertSame('parcial', $cuenta->fresh()->estado);
        $this->assertSame(300000.0, $cuenta->fresh()->saldo_pendiente);

        // Verifica que el abono quedó como un egreso real, ligado a esta cuenta.
        $this->assertDatabaseHas('movimientos_contables', [
            'cuenta_pendiente_id' => $cuenta->id,
            'tipo' => 'egreso',
            'categoria_contable_id' => $categoriaReparaciones->id,
            'monto' => 200000.00,
        ]);

        // Segundo abono que salda el total.
        Livewire::test(\App\Filament\Resources\CuentaPendienteResource\RelationManagers\MovimientosRelationManager::class, [
            'ownerRecord' => $cuenta,
            'pageClass' => \App\Filament\Resources\CuentaPendienteResource\Pages\EditCuentaPendiente::class,
        ])
            ->callTableAction('create', data: [
                'fecha' => now()->format('Y-m-d'),
                'monto' => 300000,
                'metodo_pago' => 'transferencia',
            ]);

        $this->assertSame('pagada', $cuenta->fresh()->estado);
        $this->assertSame(0.0, $cuenta->fresh()->saldo_pendiente);
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
        $this->get('/admin/cuentas-bancarias')->assertForbidden();
        $this->get('/admin/cuentas-pendientes')->assertForbidden();
    }

    public function test_lider_de_red_no_puede_ver_ni_editar_persona_o_punto_fuera_de_su_rama_por_url(): void
    {
        // Regresión IDOR: PersonaResource/PuntoConexionResource ya filtran la
        // tabla por alcance, pero eso no protege por sí solo contra alguien
        // escribiendo directamente /admin/personas/{id}/edit de una persona
        // ajena. Aquí hay DOS capas: 1) getEloquentQuery() ya scopeadas hacen
        // que el registro ajeno ni siquiera se encuentre (404), y 2)
        // PersonaPolicy/PuntoConexionPolicy::dentroDelAlcance() como respaldo
        // por si algún día se accede al modelo sin pasar por esa query
        // scopeada. Este test verifica ambas capas.
        [$red, $carlos, , , $marta] = $this->crearDatosDemo();

        $puntoDeMarta = PuntoConexion::create([
            'nombre' => 'Punto de Marta', 'red_id' => $red->id, 'lider_id' => $marta->id, 'dia_semana' => 'jueves',
        ]);

        $liderUser = User::factory()->create();
        $liderUser->assignRole('lider_red');
        $carlos->update(['user_id' => $liderUser->id]);
        $this->actingAs($liderUser);

        // Capa 1 (query scopeada): Marta no está en la rama de Carlos, así que
        // ni siquiera se encuentra el registro por esa URL → 404, no 403.
        $this->get("/admin/personas/{$marta->id}/edit")->assertNotFound();
        $this->get("/admin/puntos-conexion/{$puntoDeMarta->id}/edit")->assertNotFound();

        // Confirmamos que sí puede entrar a un registro que sí está en su rama,
        // para descartar que el 404 de arriba sea por otra razón (ej. permiso mal seedeado).
        $this->get("/admin/personas/{$carlos->id}/edit")->assertOk();

        // Capa 2 (política, independiente del query scoping): si algún día el
        // scoping de la query cambia, esta sigue negando el acceso por su cuenta.
        $this->assertFalse($liderUser->can('update', $marta));
        $this->assertFalse($liderUser->can('view', $marta));
        $this->assertFalse($liderUser->can('update', $puntoDeMarta));
        $this->assertTrue($liderUser->can('update', $carlos));
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

    public function test_personas_se_ordenan_jerarquicamente_por_red_y_rama(): void
    {
        [$red, $carlos, $andres, $pedro, $marta] = $this->crearDatosDemo();

        $sinRed = Persona::create(['nombres' => 'Zulema', 'apellidos' => 'SinRed', 'estado' => 'nuevo']);

        $ids = Persona::idsEnOrdenJerarquico();

        // Hombres (Carlos y su rama) antes que Mujeres (Marta), por orden alfabético de red;
        // dentro de Hombres, Carlos (líder principal) primero, luego sus hijos alfabéticamente;
        // quien no tiene red va al final.
        $this->assertSame(
            [$carlos->id, $andres->id, $pedro->id, $marta->id, $sinRed->id],
            $ids
        );

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\PersonaResource\Pages\ListPersonas::class)
            ->assertOk()
            ->assertSeeInOrder(['Carlos Ramírez', 'Andrés Torres', 'Pedro Gómez', 'Marta Suárez', 'Zulema SinRed']);
    }

    public function test_registrar_reunion_y_marcar_asistencia_de_un_punto_de_conexion(): void
    {
        [$red, $carlos, $andres, $pedro] = $this->crearDatosDemo();

        $punto = \App\Models\PuntoConexion::where('nombre', 'Punto de prueba')->firstOrFail();
        $punto->miembros()->attach([$andres->id, $pedro->id], ['fecha_ingreso' => now()]);

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $manager = Livewire::test(\App\Filament\Resources\PuntoConexionResource\RelationManagers\SesionesRelationManager::class, [
            'ownerRecord' => $punto,
            'pageClass' => \App\Filament\Resources\PuntoConexionResource\Pages\EditPuntoConexion::class,
        ]);

        $manager->callTableAction('create', data: [
            'fecha' => now()->format('Y-m-d'),
            'notas' => 'Primera reunión de prueba',
        ]);

        $sesion = \App\Models\SesionPuntoConexion::where('punto_conexion_id', $punto->id)->firstOrFail();

        $manager->callTableAction('asistencia', $sesion, data: [
            'presentes' => [$andres->id],
        ]);

        $this->assertDatabaseHas('asistencias_punto_conexion', [
            'sesion_punto_conexion_id' => $sesion->id,
            'persona_id' => $andres->id,
            'asistio' => true,
        ]);
        $this->assertDatabaseHas('asistencias_punto_conexion', [
            'sesion_punto_conexion_id' => $sesion->id,
            'persona_id' => $pedro->id,
            'asistio' => false,
        ]);

        $this->get("/admin/puntos-conexion/{$punto->id}/edit")->assertStatus(200);
    }

    public function test_adjuntar_miembro_a_punto_de_conexion_no_truena_al_buscar(): void
    {
        // Regresión: Filament adivina el nombre de la relación inversa como
        // "puntoConexions" (plural ingenuo de PuntoConexion), pero en Persona
        // se llama "puntosConexion". Sin ->inverseRelationship('puntosConexion')
        // en MiembrosRelationManager, buscar en el modal de "Adjuntar" truena
        // con BadMethodCallException. Este test verifica que la relación
        // inversa quede bien configurada y que la búsqueda del AttachAction
        // no lance ninguna excepción.
        [$red, $carlos, $andres] = $this->crearDatosDemo();

        $punto = PuntoConexion::where('nombre', 'Punto de prueba')->firstOrFail();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $manager = Livewire::test(\App\Filament\Resources\PuntoConexionResource\RelationManagers\MiembrosRelationManager::class, [
            'ownerRecord' => $punto,
            'pageClass' => \App\Filament\Resources\PuntoConexionResource\Pages\EditPuntoConexion::class,
        ]);

        $this->assertSame('puntosConexion', $manager->instance()->getTable()->getInverseRelationship());

        $manager->mountTableAction('attach');

        // Esto es justo el flujo que reportó el error original: buscar dentro
        // del select de "Adjuntar" dispara whereDoesntHave('puntosConexion', ...).
        $resultados = $manager->instance()->getTable()
            ->getInverseRelationship()
            ? Persona::query()->whereDoesntHave('puntosConexion')->where('nombres', 'like', '%Andr%')->get()
            : null;

        $this->assertNotNull($resultados);
        $this->assertTrue($resultados->contains($andres));
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

    public function test_crear_persona_desde_el_panel_exige_confirmar_autorizacion_de_datos(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        // Sin marcar la casilla, la validación required() bloquea el guardado.
        Livewire::test(\App\Filament\Resources\PersonaResource\Pages\CreatePersona::class)
            ->fillForm(['nombres' => 'Ana', 'apellidos' => 'Registrada', 'estado' => 'nuevo'])
            ->call('create')
            ->assertHasFormErrors(['autorizacion_confirmada']);

        $this->assertDatabaseMissing('personas', ['nombres' => 'Ana', 'apellidos' => 'Registrada']);

        // Marcándola, se crea la persona y queda la evidencia de autorización.
        Livewire::test(\App\Filament\Resources\PersonaResource\Pages\CreatePersona::class)
            ->fillForm(['nombres' => 'Ana', 'apellidos' => 'Registrada', 'estado' => 'nuevo', 'autorizacion_confirmada' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $persona = Persona::where('nombres', 'Ana')->firstOrFail();
        $this->assertDatabaseHas('autorizaciones_tratamiento_datos', [
            'persona_id' => $persona->id,
            'canal' => 'registro_manual',
            'registrado_por_user_id' => $admin->id,
        ]);
    }

    public function test_editar_persona_sin_autorizacion_previa_la_exige_pero_no_vuelve_a_pedirla(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        // Persona creada por fuera del flujo del panel (ej. importada): sin evidencia todavía.
        $persona = Persona::create(['nombres' => 'Sin', 'apellidos' => 'Evidencia', 'estado' => 'nuevo']);

        Livewire::test(\App\Filament\Resources\PersonaResource\Pages\EditPersona::class, ['record' => $persona->getRouteKey()])
            ->assertFormFieldIsVisible('autorizacion_confirmada')
            ->fillForm(['autorizacion_confirmada' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('autorizaciones_tratamiento_datos', [
            'persona_id' => $persona->id,
            'canal' => 'registro_manual',
        ]);

        // Ya con evidencia, la casilla no vuelve a aparecer en ediciones futuras.
        Livewire::test(\App\Filament\Resources\PersonaResource\Pages\EditPersona::class, ['record' => $persona->getRouteKey()])
            ->assertFormFieldIsHidden('autorizacion_confirmada');

        $this->assertSame(1, $persona->autorizacionesTratamientoDatos()->count());
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

    public function test_alertas_detecta_personas_sin_retomar_puntos_sin_reportar_y_cumpleanos(): void
    {
        [$red, $carlos] = $this->crearDatosDemo();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        // Persona nueva, nunca contactada: debe salir (referencia = fecha_primera_visita, hace 40 días).
        $sinContactar = Persona::create([
            'nombres' => 'Nueva', 'apellidos' => 'SinContactar', 'estado' => 'nuevo',
            'fecha_primera_visita' => now()->subDays(40),
        ]);

        // En seguimiento, con nota reciente: NO debe salir (contacto hace 5 días).
        $conNotaReciente = Persona::create(['nombres' => 'Reciente', 'apellidos' => 'ConNota', 'estado' => 'en_seguimiento']);
        \App\Models\NotaSeguimiento::create([
            'persona_id' => $conNotaReciente->id, 'user_id' => $admin->id, 'fecha' => now()->subDays(5), 'nota' => 'Llamada reciente',
        ]);

        // En seguimiento, con nota vieja: debe salir (contacto hace 35 días).
        $conNotaVieja = Persona::create(['nombres' => 'Vieja', 'apellidos' => 'ConNota', 'estado' => 'en_seguimiento']);
        \App\Models\NotaSeguimiento::create([
            'persona_id' => $conNotaVieja->id, 'user_id' => $admin->id, 'fecha' => now()->subDays(35), 'nota' => 'Visita antigua',
        ]);

        // En red: nunca debe salir en esta lista, sin importar cuánto tiempo lleve.
        Persona::create(['nombres' => 'YaIntegrada', 'apellidos' => 'EnRed', 'estado' => 'en_red']);

        $sinReportar = PuntoConexion::create([
            'nombre' => 'Punto nunca reportado', 'red_id' => $red->id, 'lider_id' => $carlos->id, 'dia_semana' => 'lunes',
        ]);

        $puntoAlDia = PuntoConexion::where('nombre', 'Punto de prueba')->firstOrFail();
        $puntoAlDia->sesiones()->create(['fecha' => now()->subDays(2)]);

        $puntoAtrasado = PuntoConexion::create([
            'nombre' => 'Punto atrasado', 'red_id' => $red->id, 'lider_id' => $carlos->id, 'dia_semana' => 'miercoles',
        ]);
        $puntoAtrasado->sesiones()->create(['fecha' => now()->subDays(20)]);

        $cumpleaneroEsteMes = Persona::create([
            'nombres' => 'Cumple', 'apellidos' => 'EsteMes', 'estado' => 'en_red',
            'fecha_nacimiento' => now()->subYears(20)->startOfMonth(),
        ]);
        Persona::create([
            'nombres' => 'Cumple', 'apellidos' => 'OtroMes', 'estado' => 'en_red',
            'fecha_nacimiento' => now()->subYears(20)->addMonths(1),
        ]);

        $servicio = new \App\Services\AlertasService();

        $sinRetomarIds = $servicio->personasSinRetomar()->pluck('id');
        $this->assertTrue($sinRetomarIds->contains($sinContactar->id));
        $this->assertTrue($sinRetomarIds->contains($conNotaVieja->id));
        $this->assertFalse($sinRetomarIds->contains($conNotaReciente->id));

        $sinReportarNombres = $servicio->puntosSinReportar()->pluck('nombre');
        $this->assertTrue($sinReportarNombres->contains('Punto nunca reportado'));
        $this->assertTrue($sinReportarNombres->contains('Punto atrasado'));
        $this->assertFalse($sinReportarNombres->contains('Punto de prueba'));

        $cumpleanosIds = $servicio->cumpleanosDelMes()->pluck('id');
        $this->assertTrue($cumpleanosIds->contains($cumpleaneroEsteMes->id));

        $this->actingAs($admin);
        Livewire::test(\App\Filament\Pages\Alertas::class)
            ->assertOk()
            ->assertSee('Nueva SinContactar')
            ->assertSee('Punto nunca reportado')
            ->assertSee('Cumple EsteMes');
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
