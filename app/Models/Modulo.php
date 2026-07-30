<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Modulo extends Model
{
    protected $table = 'modulos';

    protected $fillable = [
        'slug',
        'nombre',
        'descripcion',
        'grupo',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_modulo');
    }
}
