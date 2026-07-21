<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelPermit extends Model
{
    protected $fillable = [
        'item_id',
        'character_id',
        'kingdom_id',
        'granted_by',
        'valid_days',
        'activated_at',
        'expires_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function character()
    {
        return $this->belongsTo(Character::class);
    }

    public function kingdom()
    {
        return $this->belongsTo(Kingdom::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function isActive(): bool
    {
        return $this->activated_at !== null
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }

    public function activate(): void
    {
        $this->activated_at = now();
        $this->expires_at   = now()->addDays($this->valid_days);
        $this->save();
    }
}
