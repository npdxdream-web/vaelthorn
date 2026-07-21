<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterStat extends Model
{
    protected $fillable = [
        'character_id',
        'level',
        'exp',
        'exp_to_next',
        'hp',
        'mana',
        'str',
        'agi',
        'int',
        'stat_points_available',
        'daily_exp',
        'daily_exp_date',
        'stage_1_completed',
        'stage_2_completed',
        'stage_3_completed',
        'rejection_reason',
    ];

    protected $casts = [
        'stage_1_completed' => 'boolean',
        'stage_2_completed' => 'boolean',
        'stage_3_completed' => 'boolean',
    ];

    public function character()
    {
        return $this->belongsTo(Character::class);
    }
}
