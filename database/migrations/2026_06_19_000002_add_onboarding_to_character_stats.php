<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            $table->unsignedTinyInteger('onboarding_intro_posts_count')->default(0)->after('exp_to_next');
            $table->boolean('onboarding_awakening_approved')->default(false)->after('onboarding_intro_posts_count');
            $table->boolean('onboarding_completed')->default(false)->after('onboarding_awakening_approved');
        });
    }

    public function down(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            $table->dropColumn(['onboarding_intro_posts_count', 'onboarding_awakening_approved', 'onboarding_completed']);
        });
    }
};
