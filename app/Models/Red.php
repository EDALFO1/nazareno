<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre'])]
class Red extends Model
{
    protected $table = 'redes';

    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class);
    }

    public function lideresPrincipales(): HasMany
    {
        return $this->hasMany(Persona::class)->whereNull('lider_id');
    }

    public function puntosConexion(): HasMany
    {
        return $this->hasMany(PuntoConexion::class);
    }

    public static function rules($id = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255', 'unique:redes,nombre,'.$id],
        ];
    }
}
