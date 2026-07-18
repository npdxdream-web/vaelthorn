<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Character extends Model
{
    protected $fillable = [
        'user_id',
        'city_id',
        'name',
        'backstory',
        'status',
        'avatar',
        'current_city_id',
        'gold',
        'title',
        'custom_frame',
    ];

    protected $appends = ['auto_rank', 'avatar_url'];

    protected static function booted(): void
    {
        static::saving(function (Character $character) {
            if ($character->user_id && Character::where('user_id', $character->user_id)
                    ->where('id', '!=', $character->id)
                    ->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => 'This user already has a character.',
                ]);
            }
        });
    }

    public function getAutoRankAttribute(): string
    {
        $count = $this->posts_count ?? $this->posts()->count();

        return match (true) {
            $count >= 100 => 'Legend',
            $count >= 50  => 'Veteran',
            $count >= 20  => 'Traveler',
            $count >= 5   => 'Wanderer',
            default       => 'Stranger',
        };
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        if (Str::startsWith($this->avatar, ['http://', 'https://'])) {
            return $this->avatar;
        }

        return Storage::disk('public')->url($this->avatar);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function currentCity()
    {
        return $this->belongsTo(City::class, 'current_city_id');
    }

    public function stats()
    {
        return $this->hasOne(CharacterStat::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function badges()
    {
        return $this->hasMany(CharacterBadge::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_participants')->withPivot('joined_at');
    }

    public function rewardLogs()
    {
        return $this->hasMany(RewardLog::class);
    }

    public function onboardingEntries()
    {
        return $this->hasMany(OnboardingEntry::class)->orderBy('stage');
    }
}
