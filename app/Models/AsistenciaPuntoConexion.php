<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sesion_punto_conexion_id', 'persona_id', 'asistio', 'notas'])]
class AsistenciaPuntoConexion extends Model
{
    protected $table = 'asistencias_punto_conexion';

    protected function casts(): array
    {
        return [
            'asistio' => 'boolean',
        ];
    }

    public function sesionPuntoConexion(): BelongsTo
    {
        return $this->belongsTo(SesionPuntoConexion::class);
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }
}
