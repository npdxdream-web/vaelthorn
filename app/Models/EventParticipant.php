<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventParticipant extends Model
{
    protected $fillable = ['event_id', 'character_id', 'joined_at'];

    protected $casts = ['joined_at' => 'datetime'];

    public function event() { return $this->belongsTo(Event::class); }
    public function character() { return $this->belongsTo(Character::class); }
}
