<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => UserRole::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role instanceof UserRole && $this->role->canAccessPanel();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isAtLeastAdmin(): bool
    {
        return in_array($this->role, [UserRole::SuperAdmin, UserRole::Admin]);
    }

    public function isAtLeastModerator(): bool
    {
        return in_array($this->role, [UserRole::SuperAdmin, UserRole::Admin, UserRole::Moderator]);
    }

    public function isAdminGroup(): bool
    {
        $adminRoles = [
            UserRole::SuperAdmin->value,
            UserRole::Admin->value,
            UserRole::Moderator->value,
            'staff',
            'support',
            'manager',
            'owner',
        ];

        $roleValue = $this->role instanceof \BackedEnum ? $this->role->value : (string) $this->role;

        return in_array(strtolower($roleValue), array_map('strtolower', $adminRoles), true);
    }

    public function character()
    {
        return $this->hasOne(Character::class);
    }

    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by');
    }
}
