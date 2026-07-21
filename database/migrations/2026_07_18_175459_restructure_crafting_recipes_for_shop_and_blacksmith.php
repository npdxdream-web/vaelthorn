<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // crafting_recipes had zero application code using it — safe to reshape directly.
        Schema::table('crafting_recipes', function (Blueprint $table) {
            $table->dropForeign(['material_item_id']);
            $table->dropColumn(['material_item_id', 'quantity_needed']);

            $table->string('name')->after('id');
            $table->enum('category', ['shop', 'blacksmith'])->after('name');
            $table->unsignedInteger('result_quantity')->default(1)->after('result_item_id');
            $table->unsignedInteger('gold_cost')->nullable()->after('result_quantity');
            $table->unsignedInteger('craft_duration_minutes')->nullable()->after('gold_cost');
            $table->boolean('is_active')->default(true)->after('craft_duration_minutes');
        });

        Schema::create('crafting_recipe_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('crafting_recipes')->cascadeOnDelete();
            $table->foreignId('material_item_id')->constrained('items')->cascadeOnDelete();
            $table->unsignedInteger('quantity_required');
            $table->timestamps();
        });

        Schema::create('crafting_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('crafting_recipes')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('characters')->cascadeOnDelete();
            $table->string('token')->unique();
            $table->enum('status', ['open', 'crafting', 'ready', 'claimed'])->default('open');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->foreignId('claimed_by')->nullable()->constrained('characters')->nullOnDelete();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('crafting_order_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('crafting_orders')->cascadeOnDelete();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crafting_order_contributions');
        Schema::dropIfExists('crafting_orders');
        Schema::dropIfExists('crafting_recipe_materials');

        Schema::table('crafting_recipes', function (Blueprint $table) {
            $table->dropColumn(['name', 'category', 'result_quantity', 'gold_cost', 'craft_duration_minutes', 'is_active']);
            $table->foreignId('material_item_id')->after('result_item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('quantity_needed')->default(1);
        });
    }
};
