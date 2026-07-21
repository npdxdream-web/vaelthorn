<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CraftingOrderContribution extends Model
{
    protected $fillable = [
        'order_id',
        'character_id',
        'item_id',
        'quantity',
    ];

    public function order()
    {
        return $this->belongsTo(CraftingOrder::class, 'order_id');
    }

    public function character()
    {
        return $this->belongsTo(Character::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
