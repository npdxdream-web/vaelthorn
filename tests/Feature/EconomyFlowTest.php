<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterStat;
use App\Models\CraftingOrder;
use App\Models\CraftingRecipe;
use App\Models\CraftingRecipeMaterial;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Kingdom;
use App\Models\TravelPermit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EconomyFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeCharacter(string $name, int $gold = 0): Character
    {
        $user = User::factory()->create();
        $character = $user->character()->create(['name' => $name, 'status' => 'active', 'gold' => $gold]);
        CharacterStat::create(['character_id' => $character->id, 'level' => 1, 'exp' => 0, 'exp_to_next' => 10, 'hp' => 100, 'mana' => 50, 'str' => 10, 'agi' => 10, 'int' => 10]);

        return $character;
    }

    public function test_shop_buy_with_gold_deducts_gold_and_grants_item(): void
    {
        $character = $this->makeCharacter('Buyer', gold: 100);
        $this->actingAs($character->user);

        $sword = Item::create(['name' => 'Iron Sword', 'type' => 'weapon', 'rarity' => 'common', 'is_tradeable' => true, 'is_active' => true]);
        $recipe = CraftingRecipe::create([
            'name' => 'Iron Sword Recipe', 'category' => 'shop', 'result_item_id' => $sword->id,
            'result_quantity' => 1, 'gold_cost' => 50, 'is_active' => true,
        ]);

        $this->post("/market/shop/{$recipe->id}/buy", ['payment_method' => 'gold'])->assertRedirect();

        $this->assertSame(50, $character->fresh()->gold);
        $this->assertDatabaseHas('inventories', ['character_id' => $character->id, 'item_id' => $sword->id, 'quantity' => 1]);
        $this->assertDatabaseHas('reward_logs', ['character_id' => $character->id, 'item_id' => $sword->id]);
    }

    public function test_shop_buy_with_gold_fails_when_insufficient(): void
    {
        $character = $this->makeCharacter('PoorBuyer', gold: 10);
        $this->actingAs($character->user);

        $sword = Item::create(['name' => 'Iron Sword', 'type' => 'weapon', 'rarity' => 'common', 'is_tradeable' => true, 'is_active' => true]);
        $recipe = CraftingRecipe::create([
            'name' => 'Iron Sword Recipe', 'category' => 'shop', 'result_item_id' => $sword->id,
            'result_quantity' => 1, 'gold_cost' => 50, 'is_active' => true,
        ]);

        $this->post("/market/shop/{$recipe->id}/buy", ['payment_method' => 'gold']);

        $this->assertSame(10, $character->fresh()->gold);
        $this->assertDatabaseMissing('inventories', ['character_id' => $character->id, 'item_id' => $sword->id]);
    }

    public function test_blacksmith_order_completes_with_two_contributors_and_only_contributor_can_claim(): void
    {
        $creator = $this->makeCharacter('Creator');
        $helper  = $this->makeCharacter('Helper');
        $stranger = $this->makeCharacter('Stranger');

        $ore = Item::create(['name' => 'Iron Ore', 'type' => 'material', 'rarity' => 'common', 'is_tradeable' => true, 'is_active' => true]);
        $ingot = Item::create(['name' => 'Steel Ingot', 'type' => 'material', 'rarity' => 'common', 'is_tradeable' => true, 'is_active' => true]);
        $blade = Item::create(['name' => 'Masterwork Blade', 'type' => 'weapon', 'rarity' => 'rare', 'is_tradeable' => true, 'is_active' => true]);

        $recipe = CraftingRecipe::create([
            'name' => 'Masterwork Blade', 'category' => 'blacksmith', 'result_item_id' => $blade->id,
            'result_quantity' => 1, 'craft_duration_minutes' => 30, 'is_active' => true,
        ]);
        CraftingRecipeMaterial::create(['recipe_id' => $recipe->id, 'material_item_id' => $ore->id, 'quantity_required' => 5]);
        CraftingRecipeMaterial::create(['recipe_id' => $recipe->id, 'material_item_id' => $ingot->id, 'quantity_required' => 2]);

        Inventory::create(['character_id' => $creator->id, 'item_id' => $ore->id, 'quantity' => 5]);
        Inventory::create(['character_id' => $helper->id, 'item_id' => $ingot->id, 'quantity' => 2]);

        $this->actingAs($creator->user);
        $this->post('/blacksmith/orders', ['recipe_id' => $recipe->id])->assertRedirect();
        $order = CraftingOrder::firstOrFail();
        $this->assertSame('open', $order->status);

        // Creator contributes the ore — order stays open (ingot still missing).
        $this->post("/blacksmith/orders/{$order->token}/contribute", ['item_id' => $ore->id, 'quantity' => 5])
            ->assertRedirect();
        $this->assertSame('open', $order->fresh()->status);
        $this->assertDatabaseMissing('inventories', ['character_id' => $creator->id, 'item_id' => $ore->id]);

        // Helper contributes the ingot — order auto-transitions to "crafting".
        $this->actingAs($helper->user);
        $this->post("/blacksmith/orders/{$order->token}/contribute", ['item_id' => $ingot->id, 'quantity' => 2])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('crafting', $order->status);
        $this->assertNotNull($order->ready_at);

        // Not ready yet — claim attempt by an eligible contributor is rejected.
        $this->post("/blacksmith/orders/{$order->token}/claim")->assertRedirect();
        $this->assertSame('crafting', $order->fresh()->status);

        // Fast-forward past ready_at.
        $order->update(['ready_at' => now()->subMinute()]);

        // A stranger (never contributed, isn't the creator) cannot claim.
        $this->actingAs($stranger->user);
        $this->post("/blacksmith/orders/{$order->token}/claim")->assertRedirect();
        $this->assertSame('crafting', $order->fresh()->status);
        $this->assertDatabaseMissing('inventories', ['character_id' => $stranger->id, 'item_id' => $blade->id]);

        // The helper (a contributor, not the creator) CAN claim.
        $this->actingAs($helper->user);
        $this->post("/blacksmith/orders/{$order->token}/claim")->assertRedirect();

        $order->refresh();
        $this->assertSame('claimed', $order->status);
        $this->assertSame($helper->id, $order->claimed_by);
        $this->assertDatabaseHas('inventories', ['character_id' => $helper->id, 'item_id' => $blade->id, 'quantity' => 1]);

        // Already claimed — a second claim attempt (even by the creator) is rejected.
        $this->actingAs($creator->user);
        $this->post("/blacksmith/orders/{$order->token}/claim")->assertRedirect();
        $this->assertDatabaseMissing('inventories', ['character_id' => $creator->id, 'item_id' => $blade->id]);
    }

    public function test_travel_permit_activation(): void
    {
        $character = $this->makeCharacter('Traveler');
        $kingdom = Kingdom::create(['name' => 'Kalif', 'is_active' => true]);
        $permitItem = Item::create(['name' => 'Kalif Travel Permit', 'type' => 'permit', 'rarity' => 'common', 'is_tradeable' => false, 'is_active' => true]);

        $permit = TravelPermit::create([
            'item_id' => $permitItem->id, 'character_id' => $character->id, 'kingdom_id' => $kingdom->id,
            'granted_by' => $character->user_id, 'valid_days' => 7,
        ]);

        $this->assertFalse($permit->isActive());

        $this->actingAs($character->user);
        $this->post("/inventory/permits/{$permit->id}/activate")->assertRedirect();

        $permit->refresh();
        $this->assertNotNull($permit->activated_at);
        $this->assertTrue($permit->isActive());

        // Already activated — second attempt is rejected, doesn't reset the expiry.
        $expiresAt = $permit->expires_at;
        $this->post("/inventory/permits/{$permit->id}/activate")->assertRedirect();
        $this->assertEquals($expiresAt, $permit->fresh()->expires_at);
    }

    public function test_market_listing_cannot_be_bought_twice(): void
    {
        $seller = $this->makeCharacter('Seller');
        $buyerA = $this->makeCharacter('BuyerA', gold: 100);
        $buyerB = $this->makeCharacter('BuyerB', gold: 100);

        $item = Item::create(['name' => 'Rare Gem', 'type' => 'material', 'rarity' => 'rare', 'is_tradeable' => true, 'is_active' => true]);
        $listing = \App\Models\MarketListing::create([
            'seller_id' => $seller->id, 'item_id' => $item->id, 'quantity' => 1, 'price' => 20, 'status' => 'active',
        ]);

        $this->actingAs($buyerA->user);
        $this->post("/market/{$listing->id}/buy")->assertRedirect();
        $this->assertSame(80, $buyerA->fresh()->gold);
        $this->assertSame('sold', $listing->fresh()->status);

        // The outer active() scope already 404s a second sequential purchase attempt.
        // (The lockForUpdate() inside the transaction guards the narrower window where two
        // requests both pass that outer check concurrently — not reproducible in a
        // single-threaded HTTP test, but exercised implicitly by not double-crediting gold here.)
        $this->actingAs($buyerB->user);
        $this->post("/market/{$listing->id}/buy")->assertNotFound();
        $this->assertSame(100, $buyerB->fresh()->gold);
        $this->assertDatabaseMissing('inventories', ['character_id' => $buyerB->id, 'item_id' => $item->id]);
    }
}
