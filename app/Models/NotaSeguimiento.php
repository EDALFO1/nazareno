<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['persona_id', 'user_id', 'fecha', 'nota'])]
class NotaSeguimiento extends Model
{
    protected $table = 'notas_seguimiento';

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
