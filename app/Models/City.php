<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'kingdom_id',
        'name',
        'description',
        'is_training_zone',
        'write_min_level',
        'write_min_role',
        'require_approval',
        'read_min_level',
        'read_min_role',
    ];

    protected $casts = [
        'is_training_zone' => 'boolean',
        'require_approval' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Training Zone must never be an approval zone — they would block onboarding
        static::saving(function (City $city) {
            if ($city->is_training_zone) {
                $city->require_approval = false;
            }
        });
    }

    /**
     * Check whether a user/character is permitted to write posts in this city.
     * Admin group always bypasses. Level 0 characters are not checked here —
     * the onboarding gate in ThreadController handles them first.
     */
    public function canWrite(User $user, ?Character $character): bool
    {
        if ($user->isAdminGroup()) {
            return true;
        }

        $level = $character?->stats?->level ?? 0;

        if ($this->write_min_level > 0 && $level < $this->write_min_level) {
            return false;
        }

        if ($this->write_min_role) {
            return match ($this->write_min_role) {
                'moderator' => $user->isAtLeastModerator(),
                'admin'     => $user->isAtLeastAdmin(),
                default     => true,
            };
        }

        return true;
    }

    public function kingdom()
    {
        return $this->belongsTo(Kingdom::class);
    }

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }
}
