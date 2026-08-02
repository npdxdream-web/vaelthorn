<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'character_id_1',
        'character_id_2',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function characterOne(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id_1');
    }

    public function characterTwo(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id_2');
    }
}
