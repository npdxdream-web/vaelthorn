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
        Schema::create('character_stats', function (Blueprint $table) {
            $table->id();
$table->foreignId('character_id')->constrained()->cascadeOnDelete();
$table->integer('level')->default(1);
$table->integer('hp')->default(100);
$table->integer('mana')->default(50);
$table->integer('agi')->default(10);
$table->integer('str')->default(10);
$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_stats');
    }
};
