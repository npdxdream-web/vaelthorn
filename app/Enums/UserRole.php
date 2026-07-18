<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'superadmin';
    case Admin      = 'admin';
    case Moderator  = 'moderator';
    case Player     = 'player';

    public function label(): string
    {
        return match($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin      => 'Admin',
            self::Moderator  => 'Moderator',
            self::Player     => 'Player',
        };
    }

    public function canAccessPanel(): bool
    {
        return $this !== self::Player;
    }
}
