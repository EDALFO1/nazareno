<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nombre', 'red_id', 'lider_id', 'anfitrion_persona_id',
    'dia_semana', 'hora', 'direccion', 'activo',
])]
class PuntoConexion extends Model
{
    protected $table = 'puntos_conexion';

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function red(): BelongsTo
    {
        return $this->belongsTo(Red::class);
    }

    public function lider(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'lider_id');
    }

    public function anfitrion(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'anfitrion_persona_id');
    }

    public function miembros(): BelongsToMany
    {
        return $this->belongsToMany(Persona::class, 'punto_conexion_persona')
            ->withPivot('fecha_ingreso')
            ->withTimestamps();
    }

    public function sesiones(): HasMany
    {
        return $this->hasMany(SesionPuntoConexion::class)->orderByDesc('fecha');
    }

    public static function rules($id = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'red_id' => ['required', 'exists:redes,id'],
            'lider_id' => ['required', 'exists:personas,id'],
            'anfitrion_persona_id' => ['nullable', 'exists:personas,id'],
            'dia_semana' => ['nullable', 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'],
            'hora' => ['nullable'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
