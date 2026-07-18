<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRequirement extends Model
{
    protected $fillable = ['event_id', 'req_type', 'req_key', 'min_value'];

    public function event() { return $this->belongsTo(Event::class); }
}
