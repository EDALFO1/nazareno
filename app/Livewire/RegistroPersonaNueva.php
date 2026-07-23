<?php

namespace App\Livewire;

use App\Models\Persona;
use Livewire\Component;

class RegistroPersonaNueva extends Component
{
    public string $nombres = '';

    public string $apellidos = '';

    public string $telefono = '';

    public string $direccion = '';

    public string $correo = '';

    public string $peticion_oracion = '';

    public bool $enviado = false;

    protected function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255'],
            'peticion_oracion' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function guardar(): void
    {
        $data = $this->validate();

        Persona::create([
            ...$data,
            'estado' => 'nuevo',
            'fecha_primera_visita' => now()->toDateString(),
        ]);

        $this->reset(['nombres', 'apellidos', 'telefono', 'direccion', 'correo', 'peticion_oracion']);
        $this->enviado = true;
    }

    public function render()
    {
        return view('livewire.registro-persona-nueva');
    }
}
