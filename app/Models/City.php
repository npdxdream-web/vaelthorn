<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'is_active',
    ];

    public function villages()
    {
        return $this->hasMany(Village::class);
    }

    public function characters()
    {
        return $this->hasMany(Character::class);
    }
}