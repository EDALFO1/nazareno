<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['proceso_id', 'persona_id', 'red_id', 'estado_participacion', 'sesion_retiro_id'])]
class ProcesoParticipante extends Model
{
    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function red(): BelongsTo
    {
        return $this->belongsTo(Red::class);
    }

    public function sesionRetiro(): BelongsTo
    {
        return $this->belongsTo(SesionProceso::class, 'sesion_retiro_id');
    }
}
