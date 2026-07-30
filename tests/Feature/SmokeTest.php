<?php

namespace Tests\Feature;

use App\Models\CategoriaContable;
use App\Models\CuentaBancaria;
use App\Models\CuentaPendiente;
use App\Models\MovimientoContable;
use App\Models\NotaSeguimiento;
use App\Models\Persona;
use App\Models\Proceso;
use App\Models\Proveedor;
use App\Models\PuntoConexion;
use App\Models\Red;
use App\Models\Rol;
use App\Models\TipoProceso;
use App\Models\User;
use App\Services\AlertasService;
use Database\Seeders\CategoriaContableSeeder;
use Database\Seeders\ModuloSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuloSeeder::class);
        $this->seed(RolSeeder::class);
        $this->seed(CategoriaContableSeeder::class);
    }

    // ── Registro público (sin sesión) ───────────────────────────────────

    public function test_pagina_publica_de_registro_carga_y_crea_persona_nueva(): void
    {
        $this->get('/registro')->assertStatus(200);

        $this->post('/registro', [
            'nombres' => 'Visitante',
            'apellidos' => 'De Prueba',
            'tipo_documento' => 'cedula_ciudadania',
            'numero_documento' => '1000000001',
            'telefono' => '3000000000',
            'correo' => 'visitante@example.com',
            'genero' => 'femenino',
            'fecha_nacimiento' => '1990-05-20',
            'peticion_oracion' => 'Por mi salud',
            'autorizoDatos' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('personas', [
            'nombres' => 'Visitante',
            'apellidos' => 'De Prueba',
            'estado' => 'nuevo',
            'genero' => 'femenino',
            'tipo_documento' => 'cedula_ciudadania',
            'numero_documento' => '1000000001',
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
        $this->post('/registro', [
            'nombres' => 'Nino',
            'apellidos' => 'DePrueba',
            'tipo_documento' => 'tarjeta_identidad',
            'numero_documento' => '1000000002',
            'acudiente' => 'Madre De Prueba',
            'telefono_acudiente' => '3000000001',
            'parentesco' => 'madre',
            'autorizoDatos' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('personas', [
            'nombres' => 'Nino',
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
        $this->post('/registro', [
            'nombres' => 'Sin',
            'apellidos' => 'Genero',
            'tipo_documento' => 'cedula_ciudadania',
            'numero_documento' => '1000000003',
            'genero' => '',
            'parentesco' => '',
            'autorizoDatos' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('personas', ['nombres' => 'Sin', 'apellidos' => 'Genero']);
    }

    public function test_registro_publico_bloquea_despues_de_demasiados_envios_desde_la_misma_ip(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $this->post('/registro', [
                'nombres' => "Visitante{$i}",
                'apellidos' => 'Prueba',
                'tipo_documento' => 'cedula_ciudadania',
                'numero_documento' => "200000{$i}",
                'autorizoDatos' => '1',
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(20, Persona::where('apellidos', 'Prueba')->count());

        $this->post('/registro', [
            'nombres' => 'Visitante21',
            'apellidos' => 'Prueba',
            'tipo_documento' => 'cedula_ciudadania',
            'numero_documento' => '2000021',
            'autorizoDatos' => '1',
        ])->assertSessionHasErrors(['nombres']);

        $this->assertSame(20, Persona::where('apellidos', 'Prueba')->count());
    }

    public function test_registro_publico_exige_marcar_la_autorizacion_de_datos(): void
    {
        $this->post('/registro', [
            'nombres' => 'Sin',
            'apellidos' => 'Autorizar',
            'tipo_documento' => 'cedula_ciudadania',
            'numero_documento' => '1000000009',
        ])->assertSessionHasErrors(['autorizoDatos']);

        $this->assertDatabaseMissing('personas', ['nombres' => 'Sin', 'apellidos' => 'Autorizar']);
    }

    public function test_force_logout_invalida_la_sesion_y_redirige_al_login(): void
    {
        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        $this->get('/dashboard')->assertStatus(200);
        $this->get('/force-logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    // ── Navegación general por rol ───────────────────────────────────────

    public function test_admin_principal_puede_ver_todos_los_modulos(): void
    {
        [$red, $carlos, , , , $proceso] = $this->crearDatosDemo();

        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        $this->get('/dashboard')->assertStatus(200);
        $this->get('/personas')->assertStatus(200);
        $this->get('/puntos_conexion')->assertStatus(200);
        $this->get('/procesos')->assertStatus(200);
        $this->get('/redes')->assertStatus(200);
        $this->get('/estructura-red')->assertStatus(200);
        $this->get('/usuarios')->assertStatus(200);
        $this->get('/categorias_contables')->assertStatus(200);
        $this->get('/movimientos_contables')->assertStatus(200);
        $this->get('/donaciones_activos')->assertStatus(200);
        $this->get('/proveedores')->assertStatus(200);
        $this->get('/certificado-donante')->assertStatus(200);
        $this->get('/reportes')->assertStatus(200);
        $this->get('/cuentas_bancarias')->assertStatus(200);
        $this->get('/cuentas_pendientes')->assertStatus(200);
        $this->get("/procesos/{$proceso->id}/edit")->assertStatus(200);
        $this->get("/personas/{$carlos->id}/edit")->assertStatus(200);
        $this->get("/redes/{$red->id}/edit")->assertStatus(200);
    }

    public function test_pagina_de_codigo_qr_de_registro_muestra_el_qr_y_el_enlace(): void
    {
        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        $this->get('/qr-registro')
            ->assertOk()
            ->assertSee(url('/registro'))
            ->assertSee('<svg', false);
    }

    public function test_lider_de_red_no_puede_ver_finanzas(): void
    {
        $liderUser = $this->crearUsuario('lider_red');
        $this->actingAs($liderUser);

        $this->get('/movimientos_contables')->assertRedirect('/dashboard');
        $this->get('/categorias_contables')->assertRedirect('/dashboard');
        $this->get('/donaciones_activos')->assertRedirect('/dashboard');
        $this->get('/proveedores')->assertRedirect('/dashboard');
        $this->get('/reportes')->assertRedirect('/dashboard');
        $this->get('/cuentas_bancarias')->assertRedirect('/dashboard');
        $this->get('/cuentas_pendientes')->assertRedirect('/dashboard');
    }

    public function test_lider_de_red_no_puede_gestionar_usuarios(): void
    {
        $liderUser = $this->crearUsuario('lider_red');
        $this->actingAs($liderUser);

        $this->get('/usuarios')->assertRedirect('/dashboard');
    }

    // ── Alcance: lider_red solo ve su rama ───────────────────────────────

    public function test_lider_de_red_no_puede_ver_ni_editar_persona_o_punto_fuera_de_su_rama_por_url(): void
    {
        [$red, $carlos, , , $marta] = $this->crearDatosDemo();

        $puntoDeMarta = PuntoConexion::create([
            'nombre' => 'Punto de Marta', 'red_id' => $red->id, 'lider_id' => $marta->id, 'dia_semana' => 'jueves',
        ]);

        $liderUser = $this->crearUsuario('lider_red');
        $carlos->update(['user_id' => $liderUser->id]);
        $this->actingAs($liderUser);

        $this->get("/personas/{$marta->id}/edit")->assertForbidden();
        $this->get("/puntos_conexion/{$puntoDeMarta->id}/edit")->assertForbidden();

        // Confirma que sí puede entrar a un registro que sí está en su rama.
        $this->get("/personas/{$carlos->id}/edit")->assertOk();
    }

    public function test_lider_de_red_solo_ve_su_propio_subarbol(): void
    {
        [$red, $carlos, $andres, $pedro, $marta] = $this->crearDatosDemo();

        $liderUser = $this->crearUsuario('lider_red');
        $carlos->update(['user_id' => $liderUser->id]);
        $this->actingAs($liderUser);

        $visibles = $liderUser->alcancePersonaIds();

        $this->assertContains($carlos->id, $visibles);
        $this->assertContains($andres->id, $visibles);
        $this->assertContains($pedro->id, $visibles);
        $this->assertNotContains($marta->id, $visibles);

        $this->get('/personas')->assertStatus(200);
    }

    public function test_campo_usuario_del_sistema_solo_lo_ve_admin_no_lider_de_red(): void
    {
        $liderUser = $this->crearUsuario('lider_red');
        $this->actingAs($liderUser);
        $this->get('/personas/create')->assertDontSee('Usuario del sistema');

        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);
        $this->get('/personas/create')->assertSee('Usuario del sistema');
    }

    // ── Personas: autorización de datos ──────────────────────────────────

    public function test_crear_persona_exige_confirmar_autorizacion_de_datos(): void
    {
        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        // Sin marcar la casilla, la validación bloquea el guardado.
        $this->post('/personas', ['nombres' => 'Ana', 'apellidos' => 'Registrada', 'estado' => 'nuevo'])
            ->assertSessionHasErrors(['autorizacion_confirmada']);

        $this->assertDatabaseMissing('personas', ['nombres' => 'Ana', 'apellidos' => 'Registrada']);

        // Marcándola, se crea la persona y queda la evidencia de autorización.
        $this->post('/personas', [
            'nombres' => 'Ana', 'apellidos' => 'Registrada', 'estado' => 'nuevo', 'autorizacion_confirmada' => '1',
        ])->assertSessionHasNoErrors();

        $persona = Persona::where('nombres', 'Ana')->firstOrFail();
        $this->assertDatabaseHas('autorizaciones_tratamiento_datos', [
            'persona_id' => $persona->id,
            'canal' => 'registro_manual',
            'registrado_por_user_id' => $admin->id,
        ]);
    }

    public function test_editar_persona_sin_autorizacion_previa_la_exige_pero_no_vuelve_a_pedirla(): void
    {
        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        // Persona creada por fuera del flujo normal (ej. importada): sin evidencia todavía.
        $persona = Persona::create(['nombres' => 'Sin', 'apellidos' => 'Evidencia', 'estado' => 'nuevo']);

        $this->get("/personas/{$persona->id}/edit")->assertSee('autorizacion_confirmada');

        $this->put("/personas/{$persona->id}", [
            'nombres' => 'Sin', 'apellidos' => 'Evidencia', 'estado' => 'nuevo', 'autorizacion_confirmada' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('autorizaciones_tratamiento_datos', [
            'persona_id' => $persona->id,
            'canal' => 'registro_manual',
        ]);

        // Ya con evidencia, la casilla no vuelve a aparecer en ediciones futuras.
        $this->get("/personas/{$persona->id}/edit")->assertDontSee('autorizacion_confirmada');
        $this->assertSame(1, $persona->autorizacionesTratamientoDatos()->count());
    }

    public function test_personas_se_ordenan_jerarquicamente_por_red_y_rama(): void
    {
        [$red, $carlos, $andres, $pedro, $marta] = $this->crearDatosDemo();

        $sinRed = Persona::create(['nombres' => 'Zulema', 'apellidos' => 'SinRed', 'estado' => 'nuevo']);

        $ids = Persona::idsEnOrdenJerarquico();

        // Hombres (Carlos y su rama) antes que Mujeres (Marta), por orden alfabético de red;
        // dentro de Hombres, Carlos (líder principal) primero, luego sus hijos alfabéticamente;
        // quien no tiene red va al final.
        $this->assertSame([$carlos->id, $andres->id, $pedro->id, $marta->id, $sinRed->id], $ids);

        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        $this->get('/personas')->assertSeeInOrder(['Carlos Ramírez', 'Andrés Torres', 'Pedro Gómez', 'Marta Suárez', 'Zulema SinRed']);
    }

    public function test_lineas_de_liderazgo_solo_marcan_a_quienes_lideran_a_alguien(): void
    {
        $red = Red::create(['nombre' => 'Hombres']);

        $victor = Persona::create(['nombres' => 'Victor', 'apellidos' => 'Velez', 'estado' => 'en_red', 'red_id' => $red->id]);
        $gabriel = Persona::create([
            'nombres' => 'Gabriel', 'apellidos' => 'Santacruz', 'estado' => 'en_red',
            'red_id' => $red->id, 'lider_id' => $victor->id,
        ]);
        $polanco = Persona::create([
            'nombres' => 'Victor', 'apellidos' => 'Polanco', 'estado' => 'en_seguimiento',
            'red_id' => $red->id, 'lider_id' => $gabriel->id,
        ]);

        $this->assertTrue($victor->fresh()->es_lider_principal);
        $this->assertNull($victor->fresh()->etiqueta_linea);

        $this->assertSame(1, $gabriel->fresh()->linea_liderazgo);
        $this->assertSame('1ª línea', $gabriel->fresh()->etiqueta_linea);

        $this->assertNull($polanco->fresh()->linea_liderazgo);
        $this->assertNull($polanco->fresh()->etiqueta_linea);

        Persona::create([
            'nombres' => 'Nuevo', 'apellidos' => 'Discípulo', 'estado' => 'nuevo',
            'red_id' => $red->id, 'lider_id' => $polanco->id,
        ]);

        $this->assertSame(2, $polanco->fresh()->linea_liderazgo);
        $this->assertSame('2ª línea', $polanco->fresh()->etiqueta_linea);
    }

    // ── Estructura de red ─────────────────────────────────────────────────

    public function test_estructura_de_red_muestra_la_rama_completa_del_lider_principal(): void
    {
        [$red, $carlos, $andres, $pedro, $marta] = $this->crearDatosDemo();

        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        // Nota: "Marta Suárez" sí puede aparecer en la página como opción del
        // selector de líder (lista a todos los líderes elegibles); lo que no
        // debe pasar es que sus datos aparezcan en el árbol de la rama.
        $response = $this->get("/estructura-red?lider={$carlos->id}");

        $response->assertOk()
            ->assertSee('Andrés Torres')
            ->assertSee('Pedro Gómez')
            ->assertSee('Punto de prueba');

        $arbol = \Illuminate\Support\Str::of($response->getContent())
            ->after('Árbol de discipulado')
            ->before('Puntos de conexión de la rama');
        $this->assertStringNotContainsString('Marta Suárez', (string) $arbol);
    }

    public function test_lider_de_red_ve_su_propia_rama_en_estructura_de_red_sin_seleccionar(): void
    {
        [$red, $carlos, $andres, $pedro, $marta] = $this->crearDatosDemo();

        $liderUser = $this->crearUsuario('lider_red');
        $carlos->update(['user_id' => $liderUser->id]);
        $this->actingAs($liderUser);

        // Se auto-selecciona su propia rama, sin necesidad de elegir nada.
        $this->get('/estructura-red')->assertOk()->assertSee('Andrés Torres')->assertSee('Pedro Gómez');

        // Intentar ver la rama de Marta (fuera de su alcance) por la URL no funciona:
        // el controlador ignora el parámetro y sigue mostrando su propia rama.
        $this->get("/estructura-red?lider={$marta->id}")
            ->assertOk()
            ->assertDontSee('Marta Suárez')
            ->assertSee('Andrés Torres');
    }

    // ── Procesos ──────────────────────────────────────────────────────────

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

        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        $this->post('/procesos', [
            'tipo_proceso_id' => $tipoSanidad->id,
            'nombre' => 'Sanidad Integral de prueba',
            'estado' => 'planificado',
            'cargar_desde_proceso_id' => $encuentro->id,
        ])->assertSessionHasNoErrors();

        $nuevoProceso = Proceso::where('nombre', 'Sanidad Integral de prueba')->firstOrFail();

        $this->assertSame(1, $nuevoProceso->participantes()->count());
        $this->assertTrue($nuevoProceso->participantes()->where('persona_id', $andres->id)->exists());
        $this->assertFalse($nuevoProceso->participantes()->where('persona_id', $pedro->id)->exists());
        $this->assertSame('en_curso', $nuevoProceso->participantes()->first()->estado_participacion);
    }

    // ── Puntos de conexión ────────────────────────────────────────────────

    public function test_registrar_reunion_y_marcar_asistencia_de_un_punto_de_conexion(): void
    {
        [$red, $carlos, $andres, $pedro] = $this->crearDatosDemo();

        $punto = PuntoConexion::where('nombre', 'Punto de prueba')->firstOrFail();
        $punto->miembros()->attach([$andres->id, $pedro->id], ['fecha_ingreso' => now()]);

        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        $this->post("/puntos_conexion/{$punto->id}/sesiones", [
            'fecha' => now()->format('Y-m-d'),
            'notas' => 'Primera reunión de prueba',
        ])->assertSessionHasNoErrors();

        $sesion = $punto->sesiones()->firstOrFail();

        $this->post("/sesiones-punto-conexion/{$sesion->id}/asistencia", [
            'presentes' => [$andres->id],
        ])->assertRedirect(route('puntos_conexion.show', $punto->id));

        $this->assertDatabaseHas('asistencias_punto_conexion', [
            'sesion_punto_conexion_id' => $sesion->id, 'persona_id' => $andres->id, 'asistio' => true,
        ]);
        $this->assertDatabaseHas('asistencias_punto_conexion', [
            'sesion_punto_conexion_id' => $sesion->id, 'persona_id' => $pedro->id, 'asistio' => false,
        ]);

        $this->get("/puntos_conexion/{$punto->id}")->assertStatus(200);
    }

    // ── Sistema: usuarios y roles ─────────────────────────────────────────

    public function test_crear_usuario_con_rol_lider_de_red_y_vincularlo_a_una_persona(): void
    {
        [$red, $carlos] = $this->crearDatosDemo();

        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        $rolLiderRed = Rol::where('nombre', 'lider_red')->firstOrFail();

        $this->post('/usuarios', [
            'name' => 'Carlos Ramírez',
            'email' => 'carlos.lider@ciudaddedios.test',
            'password' => 'Lider2026!',
            'rol_id' => $rolLiderRed->id,
        ])->assertSessionHasNoErrors();

        $nuevoUsuario = User::where('email', 'carlos.lider@ciudaddedios.test')->firstOrFail();

        $this->assertTrue($nuevoUsuario->hasRol('lider_red'));
        $this->assertTrue(Hash::check('Lider2026!', $nuevoUsuario->password));

        $carlos->update(['user_id' => $nuevoUsuario->id]);
        $this->assertSame($carlos->id, $nuevoUsuario->fresh()->persona->id);
    }

    // ── Finanzas ──────────────────────────────────────────────────────────

    public function test_comprobante_de_movimiento_contable_queda_en_disco_privado(): void
    {
        // Regresión: los comprobantes (recibos/facturas) son documentos
        // financieros. Deben quedar en el disco "local" (storage/app/private),
        // servidos solo mediante una URL firmada y temporal, nunca en el disco
        // "public" donde cualquiera con el enlace podría verlos sin sesión.
        Storage::fake('local');

        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        $categoria = CategoriaContable::where('tipo', 'ingreso')->firstOrFail();

        $this->post('/movimientos_contables', [
            'tipo' => 'ingreso',
            'categoria_contable_id' => $categoria->id,
            'fecha' => now()->format('Y-m-d'),
            'monto' => 50000,
            'metodo_pago' => 'efectivo',
            'comprobante' => \Illuminate\Http\UploadedFile::fake()->image('recibo.jpg'),
        ])->assertSessionHasNoErrors();

        $movimiento = MovimientoContable::where('monto', 50000)->firstOrFail();
        $this->assertNotNull($movimiento->comprobante);
        Storage::disk('local')->assertExists($movimiento->comprobante);

        // Sin sesión, ni la ruta que genera el enlace firmado es accesible.
        $this->post('/logout');
        $this->get("/movimientos_contables/{$movimiento->id}/comprobante")->assertRedirect('/login');
    }

    public function test_crear_movimiento_contable_vuelve_al_formulario_en_blanco_no_al_listado(): void
    {
        // Se suelen registrar varios movimientos seguidos (ofrendas de un
        // mismo culto), así que "Crear" debe dejar listo un formulario nuevo
        // en vez de mandar al listado.
        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        $categoria = CategoriaContable::where('tipo', 'ingreso')->firstOrFail();

        $this->post('/movimientos_contables', [
            'tipo' => 'ingreso',
            'categoria_contable_id' => $categoria->id,
            'fecha' => now()->format('Y-m-d'),
            'monto' => 50000,
            'metodo_pago' => 'efectivo',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('movimientos_contables.create'));
    }

    public function test_registrar_compra_a_un_proveedor(): void
    {
        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        $this->post('/proveedores', [
            'nombre' => 'Ferretería XYZ',
            'nit' => '900123456-1',
            'telefono' => '3101234567',
            'activo' => '1',
        ])->assertSessionHasNoErrors();

        $proveedor = Proveedor::where('nombre', 'Ferretería XYZ')->firstOrFail();

        $categoriaCompras = CategoriaContable::where('tipo', 'egreso')->where('nombre', 'Compra de insumos')->firstOrFail();

        $this->post('/movimientos_contables', [
            'tipo' => 'egreso',
            'categoria_contable_id' => $categoriaCompras->id,
            'fecha' => now()->format('Y-m-d'),
            'monto' => 85000,
            'metodo_pago' => 'efectivo',
            'proveedor_id' => $proveedor->id,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('movimientos_contables', [
            'proveedor_id' => $proveedor->id,
            'tipo' => 'egreso',
            'monto' => 85000.00,
        ]);

        $this->get('/movimientos_contables')->assertOk()->assertSee('Ferretería XYZ');
    }

    public function test_registrar_ingreso_de_diezmo_y_generar_certificado_de_donante(): void
    {
        [, $carlos] = $this->crearDatosDemo();

        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        $categoriaDiezmo = CategoriaContable::where('tipo', 'ingreso')->where('nombre', 'Diezmo')->firstOrFail();

        $this->post('/movimientos_contables', [
            'tipo' => 'ingreso',
            'categoria_contable_id' => $categoriaDiezmo->id,
            'fecha' => now()->format('Y-m-d'),
            'monto' => 150000,
            'metodo_pago' => 'consignacion',
            'referencia' => 'CONS-001',
            'persona_id' => $carlos->id,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('movimientos_contables', [
            'persona_id' => $carlos->id,
            'tipo' => 'ingreso',
            'monto' => 150000.00,
            'registrado_por_id' => $admin->id,
        ]);

        $this->get("/certificado-donante?persona_id={$carlos->id}&anio=".now()->year)
            ->assertOk()
            ->assertSee('Carlos Ramírez')
            ->assertSee(number_format(150000, 0, ',', '.'));
    }

    public function test_reporte_financiero_calcula_saldo_ingresos_y_egresos(): void
    {
        [, $carlos] = $this->crearDatosDemo();

        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        $categoriaDiezmo = CategoriaContable::where('tipo', 'ingreso')->where('nombre', 'Diezmo')->firstOrFail();
        $categoriaServicios = CategoriaContable::where('tipo', 'egreso')->where('nombre', 'Servicios públicos')->firstOrFail();

        MovimientoContable::create([
            'tipo' => 'ingreso', 'categoria_contable_id' => $categoriaDiezmo->id, 'fecha' => now(),
            'monto' => 200000, 'metodo_pago' => 'efectivo', 'persona_id' => $carlos->id,
        ]);

        MovimientoContable::create([
            'tipo' => 'egreso', 'categoria_contable_id' => $categoriaServicios->id, 'fecha' => now(),
            'monto' => 80000, 'metodo_pago' => 'transferencia', 'descripcion' => 'Recibo de energía',
        ]);

        $this->get('/reportes')
            ->assertOk()
            ->assertSee(number_format(120000, 0, ',', '.'))
            ->assertSee('Diezmo')
            ->assertSee('Servicios públicos');

        // Exportar a Excel: mismos datos, en un archivo .xlsx real con 3 hojas.
        $reporte = app(\App\Services\ReporteFinancieroExportService::class);
        $ruta = $reporte->generar(
            [
                'saldoActual' => 120000.0,
                'totalIngresos' => 200000.0,
                'totalEgresos' => 80000.0,
                'ingresosPorCategoria' => collect([['categoria' => 'Diezmo', 'total' => 200000.0, 'cantidad' => 1]]),
                'egresosPorCategoria' => collect([['categoria' => 'Servicios públicos', 'total' => 80000.0, 'cantidad' => 1]]),
                'movimientosIngreso' => MovimientoContable::where('tipo', 'ingreso')->with(['categoriaContable', 'persona'])->get(),
                'movimientosEgreso' => MovimientoContable::where('tipo', 'egreso')->with(['categoriaContable', 'persona'])->get(),
                'totalPorCobrar' => 0.0,
                'totalPorPagar' => 0.0,
            ],
            now()->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d')
        );

        $this->assertFileExists($ruta);

        $lector = new \OpenSpout\Reader\XLSX\Reader();
        $lector->open($ruta);

        $nombresHojas = [];
        foreach ($lector->getSheetIterator() as $hoja) {
            $nombresHojas[] = $hoja->getName();
        }
        $lector->close();
        unlink($ruta);

        $this->assertSame(['Resumen', 'Ingresos', 'Egresos'], $nombresHojas);
    }

    public function test_cuenta_bancaria_calcula_saldo_actual_a_partir_de_sus_movimientos(): void
    {
        [, $carlos] = $this->crearDatosDemo();

        $categoriaDiezmo = CategoriaContable::where('tipo', 'ingreso')->where('nombre', 'Diezmo')->firstOrFail();
        $categoriaServicios = CategoriaContable::where('tipo', 'egreso')->where('nombre', 'Servicios públicos')->firstOrFail();

        $cuenta = CuentaBancaria::create([
            'nombre' => 'Cuenta de Ahorros Bancolombia', 'banco' => 'Bancolombia',
            'tipo_cuenta' => 'ahorros', 'saldo_inicial' => 500000,
        ]);

        MovimientoContable::create([
            'tipo' => 'ingreso', 'categoria_contable_id' => $categoriaDiezmo->id, 'fecha' => now(),
            'monto' => 200000, 'metodo_pago' => 'consignacion', 'persona_id' => $carlos->id, 'cuenta_bancaria_id' => $cuenta->id,
        ]);

        MovimientoContable::create([
            'tipo' => 'egreso', 'categoria_contable_id' => $categoriaServicios->id, 'fecha' => now(),
            'monto' => 80000, 'metodo_pago' => 'transferencia', 'cuenta_bancaria_id' => $cuenta->id,
        ]);

        // Un movimiento en efectivo sin cuenta bancaria no debe afectar este saldo.
        MovimientoContable::create([
            'tipo' => 'ingreso', 'categoria_contable_id' => $categoriaDiezmo->id, 'fecha' => now(),
            'monto' => 999999, 'metodo_pago' => 'efectivo',
        ]);

        $this->assertSame(620000.0, (float) $cuenta->fresh()->saldo_actual);
    }

    public function test_cuenta_pendiente_por_pagar_cambia_de_estado_al_registrar_abonos(): void
    {
        $admin = $this->crearUsuario('super_admin');
        $this->actingAs($admin);

        $categoriaReparaciones = CategoriaContable::where('tipo', 'egreso')->where('nombre', 'Reparaciones y mantenimiento')->firstOrFail();

        $cuenta = CuentaPendiente::create([
            'tipo' => 'por_pagar', 'categoria_contable_id' => $categoriaReparaciones->id,
            'descripcion' => 'Reparación del techo - Ferretería XYZ', 'monto_total' => 500000, 'fecha' => now(),
        ]);

        $this->assertSame('pendiente', $cuenta->fresh()->estado);
        $this->assertSame(500000.0, $cuenta->fresh()->saldo_pendiente);

        $this->post("/cuentas_pendientes/{$cuenta->id}/abonos", [
            'fecha' => now()->format('Y-m-d'), 'monto' => 200000, 'metodo_pago' => 'efectivo',
        ])->assertSessionHasNoErrors();

        $this->assertSame('parcial', $cuenta->fresh()->estado);
        $this->assertSame(300000.0, $cuenta->fresh()->saldo_pendiente);

        $this->assertDatabaseHas('movimientos_contables', [
            'cuenta_pendiente_id' => $cuenta->id, 'tipo' => 'egreso',
            'categoria_contable_id' => $categoriaReparaciones->id, 'monto' => 200000.00,
        ]);

        $this->post("/cuentas_pendientes/{$cuenta->id}/abonos", [
            'fecha' => now()->format('Y-m-d'), 'monto' => 300000, 'metodo_pago' => 'transferencia',
        ])->assertSessionHasNoErrors();

        $this->assertSame('pagada', $cuenta->fresh()->estado);
        $this->assertSame(0.0, $cuenta->fresh()->saldo_pendiente);
    }

    // ── Alertas ───────────────────────────────────────────────────────────

    public function test_alertas_detecta_personas_sin_retomar_puntos_sin_reportar_y_cumpleanos(): void
    {
        [$red, $carlos] = $this->crearDatosDemo();

        $admin = $this->crearUsuario('super_admin');

        $sinContactar = Persona::create([
            'nombres' => 'Nueva', 'apellidos' => 'SinContactar', 'estado' => 'nuevo',
            'fecha_primera_visita' => now()->subDays(40),
        ]);

        $conNotaReciente = Persona::create(['nombres' => 'Reciente', 'apellidos' => 'ConNota', 'estado' => 'en_seguimiento']);
        NotaSeguimiento::create([
            'persona_id' => $conNotaReciente->id, 'user_id' => $admin->id, 'fecha' => now()->subDays(5), 'nota' => 'Llamada reciente',
        ]);

        $conNotaVieja = Persona::create(['nombres' => 'Vieja', 'apellidos' => 'ConNota', 'estado' => 'en_seguimiento']);
        NotaSeguimiento::create([
            'persona_id' => $conNotaVieja->id, 'user_id' => $admin->id, 'fecha' => now()->subDays(35), 'nota' => 'Visita antigua',
        ]);

        Persona::create(['nombres' => 'YaIntegrada', 'apellidos' => 'EnRed', 'estado' => 'en_red']);

        PuntoConexion::create(['nombre' => 'Punto nunca reportado', 'red_id' => $red->id, 'lider_id' => $carlos->id, 'dia_semana' => 'lunes']);

        $puntoAlDia = PuntoConexion::where('nombre', 'Punto de prueba')->firstOrFail();
        $puntoAlDia->sesiones()->create(['fecha' => now()->subDays(2)]);

        $puntoAtrasado = PuntoConexion::create(['nombre' => 'Punto atrasado', 'red_id' => $red->id, 'lider_id' => $carlos->id, 'dia_semana' => 'miercoles']);
        $puntoAtrasado->sesiones()->create(['fecha' => now()->subDays(20)]);

        $cumpleaneroEsteMes = Persona::create([
            'nombres' => 'Cumple', 'apellidos' => 'EsteMes', 'estado' => 'en_red',
            'fecha_nacimiento' => now()->subYears(20)->startOfMonth(),
        ]);
        Persona::create([
            'nombres' => 'Cumple', 'apellidos' => 'OtroMes', 'estado' => 'en_red',
            'fecha_nacimiento' => now()->subYears(20)->addMonths(1),
        ]);

        $servicio = new AlertasService();

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
        $this->get('/alertas')
            ->assertOk()
            ->assertSee('Nueva SinContactar')
            ->assertSee('Punto nunca reportado')
            ->assertSee('Cumple EsteMes');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function crearUsuario(string $rol): User
    {
        return User::factory()->create(['rol_id' => Rol::where('nombre', $rol)->value('id')]);
    }

    /**
     * @return array{0: Red, 1: Persona, 2: Persona, 3: Persona, 4: Persona, 5: Proceso}
     */
    private function crearDatosDemo(): array
    {
        $redHombres = Red::create(['nombre' => 'Hombres']);
        Red::create(['nombre' => 'Mujeres']);
        $redMujeres = Red::where('nombre', 'Mujeres')->first();

        $carlos = Persona::create(['nombres' => 'Carlos', 'apellidos' => 'Ramírez', 'estado' => 'en_red', 'red_id' => $redHombres->id]);
        $andres = Persona::create([
            'nombres' => 'Andrés', 'apellidos' => 'Torres', 'estado' => 'en_red',
            'red_id' => $redHombres->id, 'lider_id' => $carlos->id,
        ]);
        $pedro = Persona::create([
            'nombres' => 'Pedro', 'apellidos' => 'Gómez', 'estado' => 'en_red',
            'red_id' => $redHombres->id, 'lider_id' => $carlos->id,
        ]);
        $marta = Persona::create(['nombres' => 'Marta', 'apellidos' => 'Suárez', 'estado' => 'en_red', 'red_id' => $redMujeres->id]);

        PuntoConexion::create([
            'nombre' => 'Punto de prueba', 'red_id' => $redHombres->id, 'lider_id' => $carlos->id, 'dia_semana' => 'martes',
        ]);

        $tipoEncuentro = TipoProceso::create(['codigo' => 'encuentro', 'nombre' => 'Encuentro', 'numero_sesiones' => null, 'orden' => 1]);
        $proceso = Proceso::create(['tipo_proceso_id' => $tipoEncuentro->id, 'nombre' => 'Encuentro de prueba', 'estado' => 'en_curso']);
        $proceso->sesiones()->create(['numero_sesion' => 1]);
        $proceso->participantes()->create(['persona_id' => $andres->id, 'red_id' => $redHombres->id, 'estado_participacion' => 'en_curso']);

        return [$redHombres, $carlos, $andres, $pedro, $marta, $proceso];
    }
}
