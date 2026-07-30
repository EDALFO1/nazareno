<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tipo', 'nombre', 'descripcion', 'activo'])]
class CategoriaContable extends Model
{
    protected $table = 'categorias_contables';

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoContable::class);
    }

    public static function rules($id = null): array
    {
        return [
            'tipo' => ['required', 'in:ingreso,egreso'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
