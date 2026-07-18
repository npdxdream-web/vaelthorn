<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = [
        'event_id', 'item_id', 'item_quantity',
        'gold_amount', 'exp_amount', 'note',
    ];

    public function event() { return $this->belongsTo(Event::class); }
    public function item() { return $this->belongsTo(Item::class); }
    public function logs() { return $this->hasMany(RewardLog::class); }
}
