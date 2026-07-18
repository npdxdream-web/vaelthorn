<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title', 'type', 'city_id', 'created_by',
        'status', 'description', 'start_at', 'end_at', 'exp_reward',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    public function city() { return $this->belongsTo(City::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function participants() { return $this->hasMany(EventParticipant::class); }
    public function requirements() { return $this->hasMany(EventRequirement::class); }
    public function rewards() { return $this->hasMany(Reward::class); }
    public function characters() { return $this->belongsToMany(Character::class, 'event_participants'); }
}
