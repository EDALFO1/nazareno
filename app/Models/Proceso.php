<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tipo_proceso_id', 'nombre', 'fecha_inicio', 'estado'])]
class Proceso extends Model
{
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
        ];
    }

    public function tipoProceso(): BelongsTo
    {
        return $this->belongsTo(TipoProceso::class);
    }

    public function sesiones(): HasMany
    {
        return $this->hasMany(SesionProceso::class)->orderBy('numero_sesion');
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(ProcesoParticipante::class);
    }
}
