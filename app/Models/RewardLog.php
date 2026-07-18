<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RewardLog extends Model
{
    protected $fillable = [
        'character_id', 'event_id', 'reward_id', 'item_id',
        'item_quantity', 'gold_received', 'exp_received', 'given_at',
        'post_id', 'revoked', 'revoked_at', 'revoked_by',
    ];

    protected $casts = [
        'given_at'   => 'datetime',
        'revoked'    => 'boolean',
        'revoked_at' => 'datetime',
    ];

    public function character() { return $this->belongsTo(Character::class); }
    public function event() { return $this->belongsTo(Event::class); }
    public function reward() { return $this->belongsTo(Reward::class); }
    public function item() { return $this->belongsTo(Item::class); }
    public function revokedBy() { return $this->belongsTo(User::class, 'revoked_by'); }

    // Include soft-deleted posts so the audit log always has a reference
    public function post() { return $this->belongsTo(Post::class)->withoutGlobalScope(SoftDeletingScope::class); }
}
