<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketListing extends Model
{
    protected $fillable = ['seller_id', 'item_id', 'quantity', 'price', 'status'];

    public function seller()
    {
        return $this->belongsTo(Character::class, 'seller_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
