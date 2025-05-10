<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasTenants
{

    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'is_admin',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    /**
     * Vérifie si l'utilisateur est administrateur.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === 1;
    }


    /**
     * Relation avec les districts.
     *
     * @return BelongsToMany
     */
    public function districts(): BelongsToMany
    {
        return $this->belongsToMany(District::class)
            ->withTimestamps()
            ->orderBy('name');
    }


    public function canAccessPanel(Panel $panel): bool
    {
        // Seuls les administrateurs peuvent accéder au panel admin
        if ($panel->getId() === 'admin') {
            return $this->is_admin;
        }

        // Seuls les utilisateurs non-administrateurs peuvent accéder au panel app
        if ($panel->getId() === 'app') {
            return !$this->is_admin;
        }

        // Par défaut, refuser l'accès aux autres panels
        return false;

    }


    public function canAccessTenant(Model $tenant): bool
    {
        if (!$tenant instanceof District) {
            return false;
        }
        // L'administrateur a accès à tous les tenants
        if ($this->isAdmin()) {
            return true;
        }

        return $this->districts()->get()->contains($tenant);
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->districts()->get();
    }
}
