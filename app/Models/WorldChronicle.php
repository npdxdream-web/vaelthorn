<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldChronicle extends Model
{
    protected $fillable = ['event_id', 'content', 'generated_at', 'is_published'];

    protected $casts = [
        'generated_at' => 'datetime',
        'is_published'  => 'boolean',
    ];

    public function event() { return $this->belongsTo(Event::class); }
}
