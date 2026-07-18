<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'name', 'type', 'rarity', 'description',
        'bonus_str', 'bonus_agi', 'bonus_int', 'bonus_hp', 'bonus_mana',
        'is_tradeable', 'is_active',
    ];

    public function inventories() { return $this->hasMany(Inventory::class); }
    public function rewards() { return $this->hasMany(Reward::class); }
}
