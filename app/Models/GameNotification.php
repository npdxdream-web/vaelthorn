<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = ['type', 'target_id', 'message', 'channel', 'sent_at', 'is_read'];

    protected $casts = [
        'sent_at'  => 'datetime',
        'is_read'  => 'boolean',
    ];

    /** Scope: notifications for a specific character (or broadcast) */
    public function scopeForCharacter($query, int $characterId)
    {
        return $query->where(function ($q) use ($characterId) {
            $q->where('target_id', $characterId)->orWhereNull('target_id');
        })->where('channel', 'in_app');
    }
}
