<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            // New onboarding fields — replaces the old 3-column approach in logic only
            // (old columns onboarding_intro_posts_count, onboarding_awakening_approved,
            //  onboarding_completed remain in DB but are no longer used)
            $table->boolean('stage_a_completed')->default(false)->after('onboarding_completed');
            $table->unsignedSmallInteger('stage_b_exp')->default(0)->after('stage_a_completed');
        });
    }

    public function down(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            $table->dropColumn(['stage_a_completed', 'stage_b_exp']);
        });
    }
};
