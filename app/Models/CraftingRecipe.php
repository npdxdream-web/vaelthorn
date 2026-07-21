<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CraftingRecipe extends Model
{
    protected $fillable = [
        'name',
        'category',
        'result_item_id',
        'result_quantity',
        'gold_cost',
        'craft_duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function resultItem()
    {
        return $this->belongsTo(Item::class, 'result_item_id');
    }

    public function materials()
    {
        return $this->hasMany(CraftingRecipeMaterial::class, 'recipe_id');
    }

    public function orders()
    {
        return $this->hasMany(CraftingOrder::class, 'recipe_id');
    }
}
