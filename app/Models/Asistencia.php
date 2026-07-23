<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sesion_proceso_id', 'persona_id', 'asistio', 'notas'])]
class Asistencia extends Model
{
    protected function casts(): array
    {
        return [
            'asistio' => 'boolean',
        ];
    }

    public function sesionProceso(): BelongsTo
    {
        return $this->belongsTo(SesionProceso::class);
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }
}
