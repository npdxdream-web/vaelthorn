<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterBadge extends Model
{
    protected $fillable = [
        'character_id',
        'badge_id',
        'acquired_at',
    ];

    protected $casts = ['acquired_at' => 'datetime'];

    public function character()
    {
        return $this->belongsTo(Character::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }
}