<?php

use App\Http\Controllers\AlertaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaContableController;
use App\Http\Controllers\CertificadoDonanteController;
use App\Http\Controllers\CodigoQrRegistroController;
use App\Http\Controllers\CuentaBancariaController;
use App\Http\Controllers\CuentaPendienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonacionActivoController;
use App\Http\Controllers\EstructuraRedController;
use App\Http\Controllers\ModuloRolController;
use App\Http\Controllers\MovimientoContableController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\PuntoConexionController;
use App\Http\Controllers\RedController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\ReporteFinancieroController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('dashboard'));

Route::view('/login', 'modules.auth.login')->name('login');

Route::post('/logear', [AuthController::class, 'logear'])->name('logear');

Route::get('/registro', [RegistroController::class, 'create'])->name('registro');
Route::post('/registro', [RegistroController::class, 'store'])->name('registro.store');

Route::get('/force-logout', function (Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->middleware('web');

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Los módulos de cada fase (Personas, Redes, Finanzas, Sistema, páginas a
    // medida) se agregan aquí, cada uno detrás de su propio
    // Route::middleware('modulo:slug'), a medida que se construyen.

    Route::middleware('modulo:redes')->group(function () {
        Route::resource('redes', RedController::class)->parameters(['redes' => 'red']);
    });

    Route::middleware('modulo:categorias_contables')->group(function () {
        Route::resource('categorias_contables', CategoriaContableController::class)
            ->parameters(['categorias_contables' => 'categoria_contable']);
    });

    Route::middleware('modulo:cuentas_bancarias')->group(function () {
        Route::resource('cuentas_bancarias', CuentaBancariaController::class)
            ->parameters(['cuentas_bancarias' => 'cuenta_bancaria']);
    });

    Route::middleware('modulo:donaciones_activos')->group(function () {
        Route::resource('donaciones_activos', DonacionActivoController::class)
            ->parameters(['donaciones_activos' => 'donaciones_activo']);
    });

    Route::middleware('modulo:personas')->group(function () {
        Route::get('/personas/buscar', [PersonaController::class, 'buscar'])->name('personas.buscar');
        Route::resource('personas', PersonaController::class);

        Route::post('personas/{persona}/notas', [PersonaController::class, 'agregarNota'])->name('personas.notas.store');
        Route::delete('notas-seguimiento/{nota_seguimiento}', [PersonaController::class, 'destroyNota'])->name('personas.notas.destroy');

        Route::post('personas/{persona}/procesos', [PersonaController::class, 'agregarProceso'])->name('personas.procesos.store');
    });

    // Compartidas entre las páginas de Persona y de Proceso (ambas listan y
    // mutan ProcesoParticipante); el control de acceso real lo hace
    // assertDentroDeAlcance() en el controlador, no un modulo de menú.
    Route::put('proceso-participantes/{proceso_participante}', [ProcesoController::class, 'actualizarParticipante'])->name('procesos.participantes.update');
    Route::delete('proceso-participantes/{proceso_participante}', [ProcesoController::class, 'destroyParticipante'])->name('procesos.participantes.destroy');

    Route::middleware('modulo:procesos')->group(function () {
        Route::resource('procesos', ProcesoController::class);

        Route::post('procesos/{proceso}/sesiones', [ProcesoController::class, 'agregarSesion'])->name('procesos.sesiones.store');
        Route::post('procesos/{proceso}/sesiones/generar', [ProcesoController::class, 'generarSesiones'])->name('procesos.sesiones.generar');
        Route::delete('sesiones-proceso/{sesion_proceso}', [ProcesoController::class, 'destroySesion'])->name('sesiones-proceso.destroy');
        Route::get('sesiones-proceso/{sesion_proceso}/asistencia', [ProcesoController::class, 'asistenciaForm'])->name('sesiones-proceso.asistencia');
        Route::post('sesiones-proceso/{sesion_proceso}/asistencia', [ProcesoController::class, 'asistenciaStore'])->name('sesiones-proceso.asistencia.store');

        Route::post('procesos/{proceso}/participantes', [ProcesoController::class, 'agregarParticipante'])->name('procesos.participantes.store');
        Route::get('procesos/{proceso}/marcar-terminacion', [ProcesoController::class, 'marcarTerminacionForm'])->name('procesos.marcar-terminacion');
        Route::post('procesos/{proceso}/marcar-terminacion', [ProcesoController::class, 'marcarTerminacion'])->name('procesos.marcar-terminacion.store');
    });

    Route::middleware('modulo:cuentas_pendientes')->group(function () {
        Route::resource('cuentas_pendientes', CuentaPendienteController::class)
            ->parameters(['cuentas_pendientes' => 'cuentas_pendiente']);

        Route::post('cuentas_pendientes/{cuentas_pendiente}/abonos', [CuentaPendienteController::class, 'agregarAbono'])
            ->name('cuentas_pendientes.abonos.store');
    });

    Route::middleware('modulo:proveedores')->group(function () {
        Route::resource('proveedores', ProveedorController::class)
            ->parameters(['proveedores' => 'proveedor'])
            ->except('show');
    });

    Route::middleware('modulo:movimientos_contables')->group(function () {
        Route::resource('movimientos_contables', MovimientoContableController::class)
            ->parameters(['movimientos_contables' => 'movimientos_contable'])
            ->except('show');

        Route::get('movimientos_contables/{movimientos_contable}/comprobante', [MovimientoContableController::class, 'verComprobante'])
            ->name('movimientos_contables.comprobante');
    });

    Route::middleware('modulo:puntos_conexion')->group(function () {
        Route::resource('puntos_conexion', PuntoConexionController::class);

        Route::post('puntos_conexion/{puntos_conexion}/miembros', [PuntoConexionController::class, 'agregarMiembro'])->name('puntos_conexion.miembros.store');
        Route::delete('puntos_conexion/{puntos_conexion}/miembros/{persona}', [PuntoConexionController::class, 'quitarMiembro'])->name('puntos_conexion.miembros.destroy');

        Route::post('puntos_conexion/{puntos_conexion}/sesiones', [PuntoConexionController::class, 'agregarSesion'])->name('puntos_conexion.sesiones.store');
        Route::delete('sesiones-punto-conexion/{sesion_punto_conexion}', [PuntoConexionController::class, 'destroySesion'])->name('sesiones-punto-conexion.destroy');
        Route::get('sesiones-punto-conexion/{sesion_punto_conexion}/asistencia', [PuntoConexionController::class, 'asistenciaForm'])->name('sesiones-punto-conexion.asistencia');
        Route::post('sesiones-punto-conexion/{sesion_punto_conexion}/asistencia', [PuntoConexionController::class, 'asistenciaStore'])->name('sesiones-punto-conexion.asistencia.store');
    });

    Route::middleware('modulo:usuarios')->group(function () {
        Route::resource('usuarios', UserController::class)->except('show');
    });

    Route::middleware('modulo:roles')->group(function () {
        Route::resource('roles', RolController::class)->except('show');
    });

    Route::middleware('modulo:modulos_rol')->group(function () {
        Route::get('modulos-rol', [ModuloRolController::class, 'index'])->name('modulos-rol.index');
        Route::get('modulos-rol/{rol}/edit', [ModuloRolController::class, 'edit'])->name('modulos-rol.edit');
        Route::put('modulos-rol/{rol}', [ModuloRolController::class, 'update'])->name('modulos-rol.update');
    });

    Route::middleware('modulo:alertas')->group(function () {
        Route::get('alertas', [AlertaController::class, 'index'])->name('alertas.index');
    });

    Route::middleware('modulo:estructura_red')->group(function () {
        Route::get('estructura-red', [EstructuraRedController::class, 'index'])->name('estructura-red.index');
    });

    Route::middleware('modulo:codigo_qr_registro')->group(function () {
        Route::get('qr-registro', [CodigoQrRegistroController::class, 'index'])->name('qr-registro.index');
    });

    Route::middleware(['modulo:certificado_donante', 'rol:super_admin,admin_general'])->group(function () {
        Route::get('certificado-donante', [CertificadoDonanteController::class, 'index'])->name('certificado-donante.index');
    });

    Route::middleware(['modulo:reportes', 'rol:super_admin,admin_general'])->group(function () {
        Route::get('reportes', [ReporteFinancieroController::class, 'index'])->name('reportes.index');
        Route::get('reportes/exportar', [ReporteFinancieroController::class, 'exportar'])->name('reportes.exportar');
    });

});
