<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function modulos(): BelongsToMany
    {
        return $this->belongsToMany(Modulo::class, 'rol_modulo');
    }

    public static function rules($id = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255', 'unique:roles,nombre,'.$id],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
