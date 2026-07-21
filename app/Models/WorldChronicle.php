<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WorldChronicle extends Model
{
    protected $fillable = ['event_id', 'kingdom_id', 'title', 'category', 'cover_image', 'content', 'generated_at', 'is_published'];

    protected $casts = [
        'generated_at' => 'datetime',
        'is_published'  => 'boolean',
    ];

    public function event() { return $this->belongsTo(Event::class); }

    public function kingdom() { return $this->belongsTo(Kingdom::class); }

    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?? $this->event?->title ?? 'Chronicle';
    }

    /** Directly-tagged Kingdom takes priority; falls back to the linked Event's Kingdom, if any. */
    public function getDisplayKingdomAttribute(): ?Kingdom
    {
        return $this->kingdom ?? $this->event?->kingdom;
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }
}
