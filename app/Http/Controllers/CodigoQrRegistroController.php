<?php

namespace App\Http\Controllers;

class CodigoQrRegistroController extends Controller
{
    public function index()
    {
        $titulo = 'Código QR para registro de personas';
        $urlRegistro = url('/registro');

        return view('modules.codigo_qr_registro.index', compact('titulo', 'urlRegistro'));
    }
}
