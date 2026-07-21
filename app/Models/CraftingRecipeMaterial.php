<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CraftingRecipeMaterial extends Model
{
    protected $fillable = [
        'recipe_id',
        'material_item_id',
        'quantity_required',
    ];

    public function recipe()
    {
        return $this->belongsTo(CraftingRecipe::class, 'recipe_id');
    }

    public function materialItem()
    {
        return $this->belongsTo(Item::class, 'material_item_id');
    }
}
