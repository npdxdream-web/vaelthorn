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
        if (! $this->link_type || ! $this->link_id) {
            return null;
        }

        return match ($this->link_type) {
            'thread'         => route('thread', $this->link_id),
            'event'          => route('events.show', $this->link_id),
            'inventory'      => route('inventory'),
            'character'      => route('character.show', $this->link_id),
            'council_letter' => route('council.show', $this->link_id),
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
