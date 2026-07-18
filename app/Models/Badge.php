<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = ['name', 'icon', 'description', 'condition_type', 'condition_value'];

    public function characterBadges()
    {
        return $this->hasMany(CharacterBadge::class);
    }
}
