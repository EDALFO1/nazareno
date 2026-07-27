<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['punto_conexion_id', 'fecha', 'notas'])]
class SesionPuntoConexion extends Model
{
    protected $table = 'sesiones_punto_conexion';

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function puntoConexion(): BelongsTo
    {
        return $this->belongsTo(PuntoConexion::class);
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(AsistenciaPuntoConexion::class);
    }
}
