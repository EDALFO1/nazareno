<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'nombres', 'apellidos', 'telefono', 'direccion', 'correo', 'genero',
    'fecha_nacimiento', 'fecha_primera_visita', 'peticion_oracion', 'estado',
    'red_id', 'lider_id', 'user_id',
])]
class Persona extends Model
{
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_primera_visita' => 'date',
        ];
    }

    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn () => "{$this->nombres} {$this->apellidos}");
    }

    public function red(): BelongsTo
    {
        return $this->belongsTo(Red::class);
    }

    public function lider(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'lider_id');
    }

    public function discipulos(): HasMany
    {
        return $this->hasMany(Persona::class, 'lider_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notasSeguimiento(): HasMany
    {
        return $this->hasMany(NotaSeguimiento::class)->latest('fecha');
    }

    public function puntosConexionLiderados(): HasMany
    {
        return $this->hasMany(PuntoConexion::class, 'lider_id');
    }

    public function puntosConexion(): BelongsToMany
    {
        return $this->belongsToMany(PuntoConexion::class, 'punto_conexion_persona')
            ->withPivot('fecha_ingreso')
            ->withTimestamps();
    }

    public function procesoParticipaciones(): HasMany
    {
        return $this->hasMany(ProcesoParticipante::class);
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class);
    }

    /**
     * IDs de todos los descendientes en el árbol de discipulado (cualquier profundidad),
     * usando una CTE recursiva de MySQL. No incluye el propio id.
     *
     * @return array<int>
     */
    public function descendientesIds(): array
    {
        $rows = DB::select(<<<'SQL'
            WITH RECURSIVE arbol AS (
                SELECT id FROM personas WHERE lider_id = ?
                UNION ALL
                SELECT p.id FROM personas p
                INNER JOIN arbol a ON p.lider_id = a.id
            )
            SELECT id FROM arbol
        SQL, [$this->id]);

        return array_map(fn ($row) => $row->id, $rows);
    }

    /**
     * IDs de la persona más todos sus descendientes — útil para scoping de acceso.
     *
     * @return array<int>
     */
    public function subarbolIds(): array
    {
        return [$this->id, ...$this->descendientesIds()];
    }
}
