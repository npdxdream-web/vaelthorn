<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kingdom extends Model
{
    protected $table = 'kingdoms';

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'is_active',
        'sort_order',
    ];

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function characters()
    {
        return $this->hasMany(Character::class);
    }
}
