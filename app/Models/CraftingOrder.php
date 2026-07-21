<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CraftingOrder extends Model
{
    protected $fillable = [
        'recipe_id',
        'created_by',
        'token',
        'status',
        'started_at',
        'ready_at',
        'claimed_by',
        'claimed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ready_at'   => 'datetime',
        'claimed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (CraftingOrder $order) {
            $order->token ??= (string) Str::uuid();
        });
    }

    public function recipe()
    {
        return $this->belongsTo(CraftingRecipe::class, 'recipe_id');
    }

    public function creator()
    {
        return $this->belongsTo(Character::class, 'created_by');
    }

    public function claimant()
    {
        return $this->belongsTo(Character::class, 'claimed_by');
    }

    public function contributions()
    {
        return $this->hasMany(CraftingOrderContribution::class, 'order_id');
    }

    /**
     * Sum contributed quantity per material_item_id.
     */
    public function contributedTotals(): array
    {
        return $this->contributions()
            ->selectRaw('item_id, SUM(quantity) as total')
            ->groupBy('item_id')
            ->pluck('total', 'item_id')
            ->toArray();
    }

    public function isMaterialsComplete(): bool
    {
        $totals = $this->contributedTotals();

        foreach ($this->recipe->materials as $material) {
            if (($totals[$material->material_item_id] ?? 0) < $material->quantity_required) {
                return false;
            }
        }

        return true;
    }

    public function isReadyToClaim(): bool
    {
        return $this->status === 'crafting'
            && $this->ready_at !== null
            && $this->ready_at->isPast();
    }
}
