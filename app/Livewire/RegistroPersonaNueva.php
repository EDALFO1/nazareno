<?php

namespace App\Livewire;

use App\Models\Persona;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class RegistroPersonaNueva extends Component
{
    public string $nombres = '';

    public string $apellidos = '';

    public string $telefono = '';

    public string $direccion = '';

    public string $correo = '';

    public ?string $genero = null;

    public ?string $fecha_nacimiento = null;

    public ?string $fecha_primera_visita = null;

    public string $peticion_oracion = '';

    public string $acudiente = '';

    public string $telefono_acudiente = '';

    public ?string $parentesco = null;

    public bool $autorizoDatos = false;

    public bool $enviado = false;

    public function mount(): void
    {
        $this->fecha_primera_visita = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
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
        ];
    }

    protected function messages(): array
    {
        return [
            'autorizoDatos.accepted' => 'Debes autorizar el tratamiento de tus datos personales para continuar.',
        ];
    }

    public function guardar(): void
    {
        // Límite generoso a propósito: un voluntario puede registrar a varias
        // decenas de personas seguidas desde el mismo celular/WiFi (ver la
        // página "QR de registro"). Esto solo frena un envío automatizado
        // (bot/script) que llene el formulario cientos de veces por minuto.
        $clave = 'registro-publico:'.request()->ip();

        if (RateLimiter::tooManyAttempts($clave, 20)) {
            $this->addError('nombres', 'Se recibieron demasiados registros desde esta conexión. Intenta de nuevo en unos minutos.');

            return;
        }

        RateLimiter::hit($clave, 600);

        // Los <select> sin opción elegida mandan '' (no null); lo normalizamos
        // antes de validar para que 'nullable' + 'in:...' no choque con eso.
        $this->genero = $this->genero ?: null;
        $this->parentesco = $this->parentesco ?: null;

        $data = $this->validate();
        unset($data['autorizoDatos']);
        $data['fecha_primera_visita'] = $data['fecha_primera_visita'] ?: now()->toDateString();

        $persona = Persona::create([
            ...$data,
            'estado' => 'nuevo',
        ]);

        $persona->autorizacionesTratamientoDatos()->create([
            'canal' => 'formulario_publico',
            'ip_address' => request()->ip(),
        ]);

        $this->reset([
            'nombres', 'apellidos', 'telefono', 'direccion', 'correo', 'genero', 'fecha_nacimiento',
            'peticion_oracion', 'acudiente', 'telefono_acudiente', 'parentesco', 'autorizoDatos',
        ]);
        $this->fecha_primera_visita = now()->toDateString();
        $this->enviado = true;
    }

    public function render()
    {
        return view('livewire.registro-persona-nueva');
    }
}
