<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('slot_number'); // 1, 2, or 3
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['empty', 'filled'])->default('empty');
            $table->timestamps();

            $table->unique(['character_id', 'slot_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_slots');
    }
};
