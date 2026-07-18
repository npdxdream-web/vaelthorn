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
        Schema::create('crafting_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('material_item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('quantity_needed')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crafting_recipes');
    }
};
