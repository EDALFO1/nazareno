<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Services\AlcanceService;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function persona(): HasOne
    {
        return $this->hasOne(Persona::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
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
}
