<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['codigo', 'nombre', 'numero_sesiones', 'orden'])]
class TipoProceso extends Model
{
    protected $table = 'tipos_proceso';

    public function procesos(): HasMany
    {
        return $this->hasMany(Proceso::class);
    }
}
