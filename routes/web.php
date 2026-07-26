<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/registro', function () {
    return view('registro');
})->name('registro');

/**
 * Se llama desde el navegador (ver resources/views/filament/hooks/control-sesion-pestana.blade.php)
 * cuando una pestaña nueva hereda una cookie de sesión válida pero nunca pasó
 * por el login en esta pestaña — típicamente al reabrir el navegador después
 * de cerrarlo. Cierra la sesión de verdad en el servidor, no solo visualmente.
 */
Route::get('/admin/sesion-expirada', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/admin/login');
})->middleware('web')->name('sesion-expirada');
