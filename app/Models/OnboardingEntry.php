<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'character_id',
        'stage',
        'content',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
