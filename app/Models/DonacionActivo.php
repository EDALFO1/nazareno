<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['persona_id', 'descripcion', 'valor_estimado', 'fecha', 'ubicacion_asignada', 'notas'])]
class DonacionActivo extends Model
{
    protected $table = 'donaciones_activos';

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'valor_estimado' => 'decimal:2',
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }
}
