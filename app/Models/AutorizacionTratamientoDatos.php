<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Evidencia de que una persona (o su acudiente, si es menor de edad) autorizó
 * el tratamiento de sus datos personales, conforme a la Ley 1581 de 2012.
 * Es un registro de auditoría: no se edita ni se borra, solo se crea.
 */
#[Fillable(['persona_id', 'canal', 'registrado_por_user_id', 'ip_address'])]
class AutorizacionTratamientoDatos extends Model
{
    protected $table = 'autorizaciones_tratamiento_datos';

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }
}
