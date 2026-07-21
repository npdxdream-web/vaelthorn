<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'thread_id',
        'character_id',
        'content',
        'status',
    ];

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function character()
    {
        return $this->belongsTo(Character::class);
    }

    public function reactions()
    {
        return $this->hasMany(PostReaction::class);
    }

    public function reactionCountByType(string $type): int
    {
        return $this->reactions()->where('type', $type)->count();
    }

    public function hasReactionFrom(int $characterId, string $type): bool
    {
        return $this->reactions()
            ->where('character_id', $characterId)
            ->where('type', $type)
            ->exists();
    }
}