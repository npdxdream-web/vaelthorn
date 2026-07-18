<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['weapon', 'armor', 'consumable', 'material', 'key_item', 'currency']);
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary'])->default('common');
            $table->text('description')->nullable();
            $table->integer('bonus_str')->default(0);
            $table->integer('bonus_agi')->default(0);
            $table->integer('bonus_int')->default(0);
            $table->integer('bonus_hp')->default(0);
            $table->integer('bonus_mana')->default(0);
            $table->boolean('is_tradeable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
