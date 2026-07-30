<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

class RegistroController extends Controller
{
    public function create()
    {
        return view('registro');
    }

    public function store(Request $request)
    {
        // Límite generoso a propósito: un voluntario puede registrar a varias
        // decenas de personas seguidas desde el mismo celular/WiFi (ver la
        // página "QR de registro"). Esto solo frena un envío automatizado
        // (bot/script) que llene el formulario cientos de veces por minuto.
        $clave = 'registro-publico:'.$request->ip();

        if (RateLimiter::tooManyAttempts($clave, 20)) {
            return back()->withErrors([
                'nombres' => 'Se recibieron demasiados registros desde esta conexión. Intenta de nuevo en unos minutos.',
            ])->withInput();
        }

        RateLimiter::hit($clave, 600);

        // Los <select> sin opción elegida mandan '' (no null); lo normalizamos
        // antes de validar para que 'nullable' + 'in:...' no choque con eso.
        $request->merge([
            'genero' => $request->genero ?: null,
            'parentesco' => $request->parentesco ?: null,
        ]);

        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required', Rule::in(array_keys(Persona::TIPOS_DOCUMENTO))],
            'numero_documento' => ['required', 'string', 'max:255', 'unique:personas,numero_documento'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255'],
            'genero' => ['nullable', 'in:masculino,femenino'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'fecha_primera_visita' => ['nullable', 'date', 'before_or_equal:today'],
            'peticion_oracion' => ['nullable', 'string', 'max:2000'],
            'acudiente' => ['nullable', 'string', 'max:255'],
            'telefono_acudiente' => ['nullable', 'string', 'max:255'],
            'parentesco' => ['nullable', 'in:padre,madre,abuelo_a,tio_a,hermano_a,tutor_legal,otro'],
            'autorizoDatos' => ['accepted'],
        ], [
            'autorizoDatos.accepted' => 'Debes autorizar el tratamiento de tus datos personales para continuar.',
            'numero_documento.unique' => 'Ya existe una persona registrada con este número de documento.',
        ]);

        unset($data['autorizoDatos']);
        $data['fecha_primera_visita'] = ($data['fecha_primera_visita'] ?? null) ?: now()->toDateString();

        $persona = Persona::create([
            ...$data,
            'estado' => 'nuevo',
        ]);

        $persona->autorizacionesTratamientoDatos()->create([
            'canal' => 'formulario_publico',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('registro')->with('enviado', true);
    }
}
