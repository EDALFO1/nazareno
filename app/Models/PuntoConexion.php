<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
