<?php

namespace App\Models;

use App\Services\AlcanceService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'rol_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function persona(): HasOne
    {
        return $this->hasOne(Persona::class);
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class);
    }

    public function hasRol(string $nombre): bool
    {
        return $this->rol?->nombre === $nombre;
    }

    /**
     * @param  array<int, string>  $nombres
     */
    public function hasAnyRol(array $nombres): bool
    {
        return in_array($this->rol?->nombre, $nombres, true);
    }

    /**
     * IDs de personas visibles para este usuario, o null si no tiene restricción
     * (Admin Principal / Admin General ven todo). Cálculo real en
     * App\Services\AlcanceService.
     *
     * @return array<int>|null
     */
    public function alcancePersonaIds(): ?array
    {
        return app(AlcanceService::class)->personaIdsVisiblesPara($this);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function rules($id = null): array
    {
        return [
            'rol_id' => ['required', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$id],
            'password' => [$id ? 'nullable' : 'required', 'min:6'],
        ];
    }
}
