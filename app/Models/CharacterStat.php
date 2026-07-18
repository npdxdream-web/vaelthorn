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
        'onboarding_intro_posts_count',
        'onboarding_awakening_approved',
        'onboarding_completed',
        'stage_a_completed',
        'stage_b_exp',
        'stage_1_completed',
        'stage_2_completed',
        'stage_3_completed',
    ];

    protected $casts = [
        'onboarding_awakening_approved' => 'boolean',
        'onboarding_completed'          => 'boolean',
        'stage_a_completed'             => 'boolean',
        'stage_1_completed'             => 'boolean',
        'stage_2_completed'             => 'boolean',
        'stage_3_completed'             => 'boolean',
    ];

    public function character()
    {
        return $this->belongsTo(Character::class);
    }
}
