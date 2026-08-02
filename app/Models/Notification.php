<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'body', 'data',
        'link_type', 'link_id', 'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->link_type) {
            return null;
        }

        // 'inventory' is a static route (no id segment) — item/gold reward
        // notifications intentionally carry link_id = null, so that arm must
        // not fall through the old blanket "no link_id => no url" guard.
        return match ($this->link_type) {
            'thread'         => $this->link_id ? route('thread', $this->link_id) : null,
            'event'          => $this->link_id ? route('events.show', $this->link_id) : null,
            'inventory'      => route('inventory'),
            'character'      => $this->link_id ? route('character.show', $this->link_id) : null,
            'council_letter' => $this->link_id ? route('council.show', $this->link_id) : null,
            default          => null,
        };
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    // Backward-compatible helpers for views that predate the schema upgrade
    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    public function getSentAtAttribute(): mixed
    {
        return $this->created_at;
    }

    public function getMessageAttribute(): ?string
    {
        return $this->body ?? $this->title;
    }
}
