<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('onboarding_slots');

        Schema::table('character_stats', function (Blueprint $table) {
            $table->dropColumn([
                'onboarding_intro_posts_count',
                'onboarding_awakening_approved',
                'onboarding_completed',
                'stage_a_completed',
                'stage_b_exp',
            ]);
        });
    }

    public function down(): void
    {
        Schema::create('onboarding_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('slot_number');
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['empty', 'filled'])->default('empty');
            $table->timestamps();

            $table->unique(['character_id', 'slot_number']);
        });

        Schema::table('character_stats', function (Blueprint $table) {
            $table->unsignedTinyInteger('onboarding_intro_posts_count')->default(0);
            $table->boolean('onboarding_awakening_approved')->default(false);
            $table->boolean('onboarding_completed')->default(false);
            $table->boolean('stage_a_completed')->default(false);
            $table->unsignedSmallInteger('stage_b_exp')->default(0);
        });
    }
};
